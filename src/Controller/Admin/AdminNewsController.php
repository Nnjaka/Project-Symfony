<?php

namespace App\Controller\Admin;

use App\Entity\Import;
use App\Entity\News;
use App\Form\ImportFileType;
use App\Form\NewsType;
use App\Form\NewsImportType;
use App\Handler\NewsHandler;
use App\Repository\NewsRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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
    public function update(Request $request, NewsRepository $newsRepository, ManagerRegistry $doctrine, News $news): Response
    {
        $news = $newsRepository->find($news->getId());

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

    #[Route('/admin/news/upload', name: 'admin_news_upload')]
    public function upload(Request $request, NewsHandler $newsHandler): Response
    {
        $form = $this->createForm(ImportFileType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $newsFile = $form->get('file')->getData();

            $path = $this->getParameter('kernel.project_dir') . '/public/uploads';
            $newsFile->move($path);
            $pathToFile = $path . '/' . basename($newsFile);

            $import = new Import();
            $import->setName(basename($newsFile));
            $import->setPath($pathToFile);
            $import->setFormType(NewsImportType::class);

            $data = $newsHandler->handle($import);

            if ($data) {
                return $this->renderForm('admin/upload.html.twig', [
                    'form' => $form,
                    'errors' => $data
                ]);
            }

            return $this->redirectToRoute('admin_news_index');
        }

        return $this->renderForm('admin/upload.html.twig', [
            'form' => $form,
            'errors' => ''
        ]);
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
