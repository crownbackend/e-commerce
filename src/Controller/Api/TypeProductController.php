<?php

namespace App\Controller\Api;

use App\Entity\TypeContent;
use App\Entity\TypeProduct;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/type-product")
 * Class TypeProductController
 * @package App\Controller\Api
 */
class TypeProductController extends AbstractController
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/create-type")
     */
    public function createType(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $type = new TypeProduct();

        $type->setName($data['name']);
        foreach ($data['content'] as $datum) {
            $content = new TypeContent();
            $content->setName($datum['name']);
            $content->setTypeProduct($type);
            $this->entityManager->persist($content);
        }
        $this->entityManager->persist($type);
        $this->entityManager->flush();

        return $this->json($type);
    }
}