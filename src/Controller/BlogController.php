<?php

namespace App\Controller;

use App\Entity\Post;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class BlogController extends Controller
{
    /**
     * @Route("/blog", name="blog_index")
     */
    public function index(): Response
    {
        $repository = $this->getDoctrine()->getRepository(Post::class);
        $posts = $repository->findAll();

        return $this->render('blog/index.html.twig', [
            'controller_name' => 'BlogController',
            'posts' => $posts,
        ]);
    }

    /**
     * @Route("/{id}", name="blog_show", requirements={"id"="\d+"})
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $post = $em->getRepository(Post::class)->find($id);

        if (!$post) {
            throw $this->createNotFoundException('No post found for id ' . $id);
        }

        return $this->render('blog/show.html.twig', ['post' => $post]);
    }

    /**
     * @Route("/new",name="blog_new")
     */
    public function newAction(Request $request)
    {
        // フォームの組み立て
        $post = new Post();
        $form = $this->createFormBuilder($post)
            ->add('title')
            ->add("content")
            ->getForm();

        // PSST判定バリデーション
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // エンティティを永続化
            $post->setCreatedAt(new \DateTime());
            $post->setUpdatedAt(new \DateTime());
            $em = $this->getDoctrine()->getManager();
            $em->persist($post);
            $em->flush();

            return $this->redirectToRoute('blog_index');
        }

        return $this->render('blog/new.html.twig', [
            'post' => $post,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/edit", name="blog_edit", requirements={"id"="\d+"})
     */
    public function editAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $post = $em->getRepository(Post::class)->find($id);

        if (!$post) {
            throw $this->createNotFoundException(
                "No post found for id " . $id
            );
        };

        $form = $this->createFormBuilder($post)
            ->add('title')
            ->add('content')
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $post->setUpdatedAt(new \DateTime());
            $em->flush();

            return $this->redirectToRoute('blog_index');
        };

        return $this->render('blog/new.html.twig', [
            'post' => $post,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/delete", name="blog_delete", requirements={"id"="\d+"})
     */
    function deleteAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $post = $em->getRepository(Post::class)->find($id);

        if (!$post) {
            throw $this->createNotFoundException(
                "No post found for id " . $id
            );
        }

        // 削除
        $em->remove($post);
        $em->flush();

        return $this->redirectToRoute('blog_index');
    }
}