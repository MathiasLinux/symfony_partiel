<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Like;
use App\Entity\Post;
use App\Form\CommentType;
use App\Form\PostType;
use App\Repository\CommentRepository;
use App\Repository\LikeRepository;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PostController extends AbstractController
{
    #[Route('/actuality', name: 'app_post')]
    public function index(PostRepository $postRepository): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED');
        $posts = $postRepository->findAll();
        return $this->render('post/index.html.twig', [
            'posts' => $posts,
        ]);
    }

    #[Route('/actuality/create', name: 'app_post_create', methods: ['GET', 'POST'])]
    public function create(Security $security, Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED');
        $post = new Post();
        $user = $security->getUser();
        $postForm = $this->createForm(PostType::class, $post);
        $postForm->handleRequest($request);

        if ($postForm->isSubmitted() && $postForm->isValid()) {
            $this->denyAccessUnlessGranted('IS_AUTHENTICATED');
            $name = $postForm->get("name")->getData();
            $content = $postForm->get("content")->getData();
            $images = $postForm->get("imageFile")->getData();
            $date = new \DateTimeImmutable();
            $createdAt = $date->setTimestamp(time());
            $post->setAuthor($user)
                ->setName($name)
                ->setCreatedAt($createdAt)
                ->setContent($content);
            if (isset($images)) {
                $post->setImages($images);
            }


            $entityManager->persist($post);
            $entityManager->flush();

            return $this->redirectToRoute("app_post");
        }


        return $this->render('post/create.html.twig', [
            'postForm' => $postForm,
        ]);
    }

    #[Route('/actuality/{id}', name: 'app_post_show', methods: ['GET', 'POST'])]
    public function show(Post $post, Security $security, Request $request, EntityManagerInterface $entityManager, CommentRepository $commentRepository, LikeRepository $likeRepository): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED');
        $comment = new Comment();
        $user = $security->getUser();
        $like = count($likeRepository->findBy(["post" => $post, "liked" => true]));
        $dislike = count($likeRepository->findBy(["post" => $post, "liked" => false]));
        $commentForm = $this->createForm(CommentType::class, $comment);
        $commentForm->handleRequest($request);

        $commentsToDisplay = $commentRepository->findBy(
            ["post" => $post]
        );

        if ($commentForm->isSubmitted() && $commentForm->isValid()) {
            $content = $commentForm->get("content")->getData();
            $date = new \DateTimeImmutable();
            $createdAt = $date->setTimestamp(time());

            $comment->setPost($post)
                ->setAuthor($user)
                ->setContent($content)
                ->setCreatedAt($createdAt);
            $entityManager->persist($comment);
            $entityManager->flush();

            return $this->redirectToRoute("app_post_show", ["id" => $post->getId()]);
        }

        return $this->render('post/show.html.twig', [
            'post' => $post,
            'commentForm' => $commentForm,
            'commentToDisplay' => $commentsToDisplay,
            'like' => $like,
            'dislike' => $dislike
        ]);
    }

    #[Route('/actuality/{id}/like', name: 'app_post_like')]
    public function like(Security $security, int $id, PostRepository $postRepository, EntityManagerInterface $entityManager, LikeRepository $likeRepository): Response
    {
        $user = $security->getUser();
        $curentPost = $postRepository->find($id);

        if (!($likeRepository->findBy(["user" => $user, "post" => $curentPost]))) {
            $like = new Like();
            $like->setPost($curentPost)
                ->setLiked(true)
                ->setUser($user);

            $entityManager->persist($like);
            $entityManager->flush();
        } elseif ($likeRepository->findBy(["user" => $user, "post" => $curentPost, "liked" => false])) {
            $dislikedComment = $likeRepository->findOneBy(["user" => $user, "post" => $curentPost, "liked" => false]);
            $entityManager->remove($dislikedComment);

            $like = new Like();
            $like->setPost($curentPost)
                ->setLiked(true)
                ->setUser($user);

            $entityManager->persist($like);
            $entityManager->flush();
        } elseif (($likeRepository->findBy(["user" => $user, "post" => $curentPost, "liked" => true]))) {
            $likedComment = $likeRepository->findOneBy(["user" => $user, "post" => $curentPost, "liked" => true]);
            $entityManager->remove($likedComment);
            $entityManager->flush();
        }

        return $this->redirectToRoute("app_post_show", ["id" => $id]);
    }

    #[Route('/actuality/{id}/dislike', name: 'app_post_dislike')]
    public function dislike(Security $security, int $id, PostRepository $postRepository, EntityManagerInterface $entityManager, LikeRepository $likeRepository): Response
    {
        $user = $security->getUser();
        $curentPost = $postRepository->find($id);

        if (!($likeRepository->findBy(["user" => $user, "post" => $curentPost]))) {
            $like = new Like();
            $like->setPost($curentPost)
                ->setLiked(false)
                ->setUser($user);

            $entityManager->persist($like);
            $entityManager->flush();
        } elseif ($likeRepository->findBy(["user" => $user, "post" => $curentPost, "liked" => true])) {
            $likedComment = $likeRepository->findOneBy(["user" => $user, "post" => $curentPost, "liked" => true]);
            $entityManager->remove($likedComment);

            $like = new Like();
            $like->setPost($curentPost)
                ->setLiked(false)
                ->setUser($user);

            $entityManager->persist($like);
            $entityManager->flush();
        } elseif (($likeRepository->findBy(["user" => $user, "post" => $curentPost, "liked" => false]))) {
            $dislikedComment = $likeRepository->findOneBy(["user" => $user, "post" => $curentPost, "liked" => false]);
            $entityManager->remove($dislikedComment);
            $entityManager->flush();
        }

        return $this->redirectToRoute("app_post_show", ["id" => $id]);
    }
}
