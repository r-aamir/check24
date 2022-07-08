<?php

namespace App\Repository;

use App\LoginTypeEnum;
use App\Model\Users;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends BaseRepository<Users>
 * @method Users|null find($id, $lockMode = null, $lockVersion = null)
 * @method Users|null findOneBy(array $criteria, array $orderBy = null)
 * @method Users[]    findAll()
 * @method Users[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UsersRepository extends ServiceEntityRepository
{
    public function __construct(private ManagerRegistry $registry)
    {
        parent::__construct($registry, Users::class);
    }

    public function findOneByLogin(string $loginValue, ?LoginTypeEnum $loginType = null) : ?Users
    {
        if (null === $loginType) {
            $loginType = $this->getLoginType($loginValue);
        }

        return $this->createQueryBuilder('l')
            ->where("l.{$loginType->value}=:val")
            ->setParameter('val', $loginValue)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function update(array $bind, array $where)
    {
        $query = $this->createQueryBuilder('u')->update();

        foreach ($bind as $col => $val) {
            $query->set('u.' . $col, ':' . $col)
                  ->setParameter($col, $val);
        }

        $i = 0;
        foreach ($where as $col => $val) {
            $parameterName = 'u' . $i++;
            $whereExpr     = 'u.' . $col . '=:' . $parameterName;
            if (1 === $i) {
                $query->where($whereExpr);
            } else {
                $query->andWhere($whereExpr);
            }
            $query->setParameter($parameterName, $val);
        }

        $query->getQuery()->execute();
    }

    public function detatch(Users $user)
    {
        $this->getEntityManager()->detach($user);
    }

    private function getLoginType(string $userNameOrEmail) : LoginTypeEnum
    {
        return filter_var($userNameOrEmail, FILTER_VALIDATE_EMAIL)
                ? LoginTypeEnum::EMAIL
                : LoginTypeEnum::USERNAME;
    }
}
