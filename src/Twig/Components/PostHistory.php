<?php

namespace App\Twig\Components;

use App\Repository\PostHistoryRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent('PostHistory')]
final class PostHistory
{
    use DefaultActionTrait;

    #[LiveProp(writable: true)]
    public int $limit = 6; // Nombre de posts chargés par "loadMore"

    #[LiveProp(writable: true)]
    public int $offset = 0;

    public array $posts = [];

    public function __construct(
        private PostHistoryRepository $postHistoryRepository,
        private Security $security,
    ) {}

    public function mount(): void
    {
        $this->loadPosts();
    }

    #[LiveAction]
    public function loadMore(): void
    {
        $this->offset += $this->limit;
        $this->loadPosts();
    }

    private function loadPosts(): void
    {
        $user = $this->security->getUser();

        $query = $this->postHistoryRepository->createQueryBuilder('p')
            ->andWhere('p.owner = :user')
            ->setParameter('user', $user)
            ->orderBy('p.createdAt', 'DESC')
            ->setFirstResult(0)
            ->setMaxResults($this->offset + $this->limit)
            ->getQuery();

        $this->posts = $query->getResult();
    }
}
