<?php
/**
 * @package Newscoop
 * @copyright 2012 Sourcefabric o.p.s.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */

namespace Newscoop\Entity\Repository;

use DateTime;
use Newscoop\Datatable\Source as DatatableSource;

/**
 * Topic repository
 */
class TopicRepository extends DatatableSource
{

    public function getTopics()
    {
        $em = $this->getEntityManager();
        $queryBuilder = $em->getRepository('Newscoop\Entity\Topic')
            ->createQueryBuilder('t');

        $countQueryBuilder = $em->getRepository('Newscoop\Entity\Topic')
            ->createQueryBuilder('t')
            ->select('count(t)');

        $topicsCount = $countQueryBuilder->getQuery()->getSingleScalarResult();

        $query = $queryBuilder->getQuery();
        $query->setHint('knp_paginator.count', $topicsCount);
        
        return $query;
    }

    /**
     * Find topic options
     *
     * @return array
     */
    public function findOptions()
    {
        $query = $this->createQueryBuilder('t')
            ->select('t.id, t.name')
            ->orderBy('t.name')
            ->getQuery();


        $options = array();
        foreach ($query->getResult() as $row) {
            $options[$row['id']] = $row['name'];
        }

        return $options;
    }
}
