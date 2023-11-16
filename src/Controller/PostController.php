<?php declare(strict_types=1);

namespace App\Controller;

use DateTimeImmutable;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\{Response, Request};
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\{Post};
use App\Form\PostType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

#[Route('/', requirements: ['_locale' => 'en|pl'])]
class PostController extends AbstractController
{
    #[Route('/{_locale}', name: 'posts.index', methods: ['GET'])]
    public function index(Request $request, ManagerRegistry $doctrine, string $_locale = 'en'): Response
    {
        $posts = $doctrine->getRepository(Post::class)->findAllPosts($request->query->getInt('page', 1));

        return $this->render('post/index.html.twig', [
            'posts' => $posts
        ]);
    }

    #[Route('/{_locale}/post/new', name: 'posts.new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $post = new Post();
        $post->setTitle('Title post #1');
        $post->setContent('POst Content.');
        $post->setUser($this->getUser());
        $post->setCreatedAt(new DateTimeImmutable('now'));

        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // $post = $form->getData();

            $entityManager->persist($post);
            $entityManager->flush();

            return $this->redirectToRoute('posts.index');
        }

        return $this->render('post/new.html.twig', [
            'form' => $form
        ]);
    }

    #[Route('/{_locale}/post/{id}', name: 'posts.show', methods: ['GET'])]
    public function show(Post $post): Response
    {
        return $this->render('post/show.html.twig', [
            'post' => $post
        ]);
    }

    #[Route('/{_locale}/post/{id}/edit', name: 'posts.edit', methods: ['GET', 'POST'])]
    public function edit(Post $post, Request $request, ManagerRegistry $doctrine): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $post = $form->getData();

            $entityManager = $doctrine->getManager();
            $entityManager->persist($post);
            $entityManager->flush();

            return $this->redirectToRoute('posts.index');
        }

        return $this->render('post/edit.html.twig', [
            'form' => $form
        ]);
    }

    #[Route('/{_locale}/post/{id}/delete', name: 'posts.delete', methods: ['POST'])]
    public function delete(Post $post, ManagerRegistry $doctrine): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $entityManager = $doctrine->getManager();
        $entityManager->remove($post);
        $entityManager->flush();

        return $this->redirectToRoute('posts.index');
    }

    #[Route('/{_locale}/posts/user/{id}', name: 'posts.user', methods: ['GET'])]
    public function user($id): Response
    {
        return $this->render('post/index.html.twig');
    }

    #[Route('/{_locale}/toggleFollow/{user}', name: 'toggleFollow', methods: ['GET'])]
    public function toggleFollow($user): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        return new Response('logic for toggling like/dislike');
    }
}
