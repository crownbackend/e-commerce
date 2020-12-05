<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(UserInterface $user, string $newEncodedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }

        $user->setPassword($newEncodedPassword);
        $this->_em->persist($user);
        $this->_em->flush();
    }

    public function findByUsers($role, $filter = null)
    {
        $query = $this->createQueryBuilder('u')
            ->where('u.roles LIKE :roles')
            ->setParameter('roles', '%"'.$role.'"%');
        if($filter == 1) {
            $query->orderBy("u.createdAt", "DESC")
                ->setMaxResults(10);
        } else if($filter == 2) {
            $query->orderBy("u.lastLogin", "DESC")
                ->setMaxResults(10);
        }
        return $query->getQuery()->getResult();
    }

    public function findByUsersByLoadMore($filter, $date, $role)
    {
        $query = $this->createQueryBuilder('u')
            ->where('u.roles LIKE :roles')
            ->setParameter('roles', '%"'.$role.'"%');
        if($filter == 1) {
            $query->andWhere("u.createdAt < :date")
            ->setParameter("date", $date)
            ->orderBy("u.createdAt", "DESC")
            ->setMaxResults(10);
        } else if($filter == 2) {
            $query->andWhere("u.lastLogin < :date")
                ->setParameter("date", $date)
                ->orderBy("u.lastLogin", "DESC")
                ->setMaxResults(10);
        }
        return $query->getQuery()->getResult();
    }

    public function findSearchByUser($search)
    {
        return $this->createQueryBuilder("u")
            ->orWhere('u.email LIKE :email')
            ->orWhere('u.lastName LIKE :lastName')
            ->orWhere('u.firstName LIKE :firstName')
            ->setParameters(['email' => "%$search%", 'lastName' => "%$search%",
                'firstName' => "%$search%"])
            ->orderBy("u.createdAt", "DESC")
            ->getQuery()->getResult();
    }

    public function findByUserCount($role)
    {
        return $this->createQueryBuilder("u")
            ->select("count(u.id)")
            ->where('u.roles LIKE :roles')
            ->setParameter('roles', '%"'.$role.'"%')
            ->getQuery()->getSingleResult();

    }
}
