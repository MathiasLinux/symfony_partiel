<?php

namespace App\Controller;

use App\Entity\Group;
use App\Entity\GroupPost;
use App\Form\GroupType;
use App\Form\PostType;
use App\Repository\GroupPostRepository;
use App\Repository\GroupRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class GroupController extends AbstractController
{
    #[Route('/group', name: 'app_group')]
    public function index(GroupRepository $groupRepository): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED');
        $groups = $groupRepository->findAll();
        return $this->render('group/index.html.twig', [
            'groups' => $groups,
        ]);
    }

    #[Route('/group/new', name: 'app_group_new')]
    public function new(Security $security, EntityManagerInterface $entityManager, Request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED');
        $group = new Group();
        $user = $security->getUser();
        $groupForm = $this->createForm(GroupType::class, $group);
        $groupForm->handleRequest($request);
        if ($groupForm->isSubmitted() && $groupForm->isValid()) {
            $name = $groupForm->get("name")->getData();
            $description = $groupForm->get("description")->getData();
            $date = new \DateTimeImmutable();
            $createdAt = $date->setTimestamp(time());
            $group->setOwner($user)
                ->setName($name)
                ->setCreatedAt($createdAt)
                ->setDescription($description);

            $entityManager->persist($group);
            $entityManager->flush();

            return $this->redirectToRoute("app_group");
        }


        return $this->render('group/group.html.twig', [
            'groupForm' => $groupForm,
        ]);
    }

    #[Route('/group/{id}', name: 'app_group_show')]
    public function show(Group $group, Security $security, GroupRepository $groupRepository, int $id): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED');
        $user = $security->getUser();
        $isOwner = false;

        if ($groupRepository->findBy(["id" => $id, "owner" => $user])) {
            $isOwner = true;
        }

        return $this->render('group/show.html.twig', [
            'group' => $group,
            'isOwner' => $isOwner
        ]);
    }

    #[Route('/group/{id}/post', name: 'app_group_show_post')]
    public function showPost(GroupPostRepository $groupPostRepository): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED');
        $posts = $groupPostRepository->findAll();
        return $this->render('group/post_show.html.twig', [
            'posts' => $posts,
        ]);
    }
}
