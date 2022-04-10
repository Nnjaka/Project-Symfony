<?php

namespace App\Controller\Admin;

use App\Entity\News;
use App\Form\NewsType;
use App\Repository\NewsRepository;
use App\Service\NewsFromFile;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use SplFileInfo;

class AdminNewsController extends AbstractController
{
    #[Route('/admin/news/index', name: 'admin_news_index')]
    public function index(NewsRepository $newsRepository): Response
    {
        $news = $newsRepository->findAll();

        if (!$news) {
            throw $this->createNotFoundException(
                'Новостей нет'
            );
        }

        return $this->render('admin/news.html.twig', [
            'newsList' => $news,
        ]);
    }


    #[Route('/admin/news/show/{id}', name: 'admin_news_show')]
    public function show(string $id, NewsRepository $newsRepository): Response
    {
        $news = $newsRepository->find($id);


        if (!$news) {
            throw $this->createNotFoundException(
                'Новость не найдена'
            );
        }

        return $this->render('admin/show.html.twig', [
            'news' => $news
        ]);
    }


    #[Route('/admin/news/create', name: 'admin_news_create')]
    public function create(Request $request, ManagerRegistry $doctrine): Response
    {
        $entityManager = $doctrine->getManager();

        $news = new News();
        $form = $this->createForm(NewsType::class, $news);
        $form->handleRequest($request);



        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($news);
            $entityManager->flush();

            return $this->redirectToRoute('admin_news_index');
        }

        return $this->renderForm('admin/create.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/admin/news/update/{news}', name: 'admin_news_update')]
    public function update(Request $request, ManagerRegistry $doctrine, News $news): Response
    {
        $form = $this->createForm(NewsType::class, $news, [
            'action' => $this->generateUrl('admin_news_update', [
                'news' => $news->getId()
            ]),
            'method' => 'POST'
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $news = $form->getData();

            $entityManager = $doctrine->getManager();
            $entityManager->flush();

            return $this->redirectToRoute('admin_news_index');
        }

        return $this->renderForm('admin/create.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/admin/news/delete/{news}', name: 'admin_news_delete')]
    public function delete(ManagerRegistry $doctrine, News $news): Response
    {
        if (!$news) {
            throw $this->createNotFoundException(
                'Новость не найдена'
            );
        }

        $entityManager = $doctrine->getManager();
        $entityManager->remove($news);
        $entityManager->flush();

        return $this->redirectToRoute('admin_news_index');
    }

    #[Route('/admin/news/upload_file', name: 'admin_news_upload_file')]
    public function uploadFile(): Response
    {
        return $this->render('admin/upload.html.twig');
    }

    #[Route('/admin/news/upload', name: 'admin_news_upload')]
    public function upload(Request $request, ManagerRegistry $doctrine, NewsFromFile $getNews): Response
    {
        $entityManager = $doctrine->getManager();

        $file = $request->files->get('file');

        if (!$file) {
            throw $this->createNotFoundException(
                'Файл не найден'
            );
        }

        if (!preg_match('/(xls|xlsx)/', $file->getClientOriginalExtension())) {
            throw $this->createNotFoundException(
                'Файл должен быть в формате Excel'
            );
        };

        $path = $this->getParameter('kernel.project_dir') . '/public/uploads';
        $file->move($path);
        $pathToFile = $path . '/' . basename($file);

        $getNews->getNews($pathToFile, $entityManager);

        return $this->redirectToRoute('admin_news_index');
    }

    #[Route('/admin/news/copy{news}', name: 'admin_news_copy')]
    public function copy(ManagerRegistry $doctrine, News $news): Response
    {
        $entityManager = $doctrine->getManager();

        if (!$news) {
            throw $this->createNotFoundException(
                'Новость не найдена'
            );
        }

        $copyNews = clone $news;

        $entityManager->persist($copyNews);
        $entityManager->flush();

        return $this->redirectToRoute('admin_news_index');
    }
}
