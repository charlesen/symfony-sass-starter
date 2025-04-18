<?php

namespace App\Twig\Components;

use App\Repository\PostHistoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveListener;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent('PostHistory')]
final class PostHistory
{
    use DefaultActionTrait;

    #[LiveProp(writable: true)]
    public int $limit = 6;

    #[LiveProp(writable: true)]
    public int $offset = 0;

    #[LiveProp(writable: true)]
    public ?int $editingPostId = null;

    #[LiveProp(writable: true)]
    public ?string $editingContent = null;


    public array $posts = [];

    public int $totalPosts = 0;

    public function __construct(
        private PostHistoryRepository $postHistoryRepository,
        private EntityManagerInterface $em,
        private Security $security,
    ) {}

    public function mount(): void
    {
        $this->loadPosts();
    }

    #[LiveListener('post:generated')]
    public function refreshAfterPostGeneration(): void
    {
        $this->offset = 0;
        $this->loadPosts();
    }

    #[LiveAction]
    public function edit(int $id): void
    {
        $post = $this->postHistoryRepository->find($id);
        if (!$post || $post->getOwner() !== $this->security->getUser()) {
            return;
        }

        $this->editingPostId = $id;
        $this->editingContent = $post->getContent();
    }

    #[LiveAction]
    public function cancelEdit(): void
    {
        $this->editingPostId = null;
        $this->editingContent = null;
    }

    #[LiveAction]
    public function saveEdit(#[LiveArg] int $id, #[LiveArg] string $content): void
    {
        $post = $this->postHistoryRepository->find($id);
        if (!$post || $post->getOwner() !== $this->security->getUser()) {
            return;
        }

        $post->setContent($content);
        $this->em->flush();

        $this->editingPostId = null;
        $this->editingContent = null;

        $this->loadPosts();
    }

    #[LiveAction]
    public function loadMore(): void
    {
        $this->offset += $this->limit;
        $this->loadPosts();
    }

    #[LiveAction]
    public function toggleFavorite(#[LiveArg] int $id): void
    {
        $post = $this->postHistoryRepository->find($id);

        if (!$post || $post->getOwner() !== $this->security->getUser()) {
            return;
        }

        $post->toggleFavorite();
        $this->em->flush();

        $this->loadPosts();
    }

    private function loadPosts(): void
    {
        $user = $this->security->getUser();

        $qb = $this->postHistoryRepository->createQueryBuilder('p')
            ->andWhere('p.owner = :user')
            ->setParameter('user', $user)
            ->orderBy('p.createdAt', 'DESC');

        $this->totalPosts = count($qb->getQuery()->getResult());

        $this->posts = $qb
            ->setFirstResult(0)
            ->setMaxResults($this->offset + $this->limit)
            ->getQuery()
            ->getResult();
    }
}
