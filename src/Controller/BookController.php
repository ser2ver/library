<?php

namespace App\Controller;

use App\Entity\Author;
use App\Entity\Book;
use App\Form\BookType;
use App\Repository\AuthorRepository;
use App\Repository\BookRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/book")
 */
class BookController extends Controller
{
    /**
     * @Route("/", name="book_index", methods="GET")
     */
    public function index(Request $request, BookRepository $bookRepository, AuthorRepository $authorRepository): Response
    {
        $author = new Author();
        $books = new ArrayCollection();
        if ($request->query->has('aid')) {
            $author = $authorRepository->find($request->query->get('aid'));
            if ($author)
                $books = $author->getBooks();
        } else {
            $books = $bookRepository->findBy([], ['title' => 'ASC', 'year' => 'ASC']);
        }
        return $this->render('book/index.html.twig', [
            'books' => $books,
            'author' => $author
        ]);
    }

    /**
     * @Route("/new", name="book_new", methods="GET|POST")
     */
    public function new(Request $request, AuthorRepository $authorRepository): Response
    {
        $author = new Author();
        $book = new Book();
        if ($request->query->has('aid')) {
            $author = $authorRepository->find($request->query->get('aid'));
            if ($author)
                $book->addAuthor($author);
        }
        $form = $this->createForm(BookType::class, $book);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($book);
            $em->flush();

            return $this->redirectToRoute('book_index', ['aid' => $author->getId()]);
        }

        return $this->render('book/new.html.twig', [
            'book' => $book,
            'form' => $form->createView(),
            'author' => $author
        ]);
    }

    /**
     * @Route("/{id}", name="book_show", methods="GET")
     */
    public function show(Request $request, Book $book, AuthorRepository $authorRepository): Response
    {
        $author = new Author();
        if ($request->query->has('aid')) {
            $author = $authorRepository->find($request->query->get('aid'));
        }
        return $this->render('book/show.html.twig', [
            'book' => $book,
            'author' => $author
        ]);
    }

    /**
     * @Route("/{id}/edit", name="book_edit", methods="GET|POST")
     */
    public function edit(Request $request, Book $book, AuthorRepository $authorRepository): Response
    {
        $author = new Author();
        if ($request->query->has('aid')) {
            $author = $authorRepository->find($request->query->get('aid'));
        }
        $form = $this->createForm(BookType::class, $book);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('book_edit', ['id' => $book->getId(), 'aid' => $author->getId()]);
        }

        return $this->render('book/edit.html.twig', [
            'book' => $book,
            'form' => $form->createView(),
            'author' => $author
        ]);
    }

    /**
     * @Route("/{id}", name="book_delete", methods="DELETE")
     */
    public function delete(Request $request, Book $book, AuthorRepository $authorRepository): Response
    {
        $author = new Author();
        if ($request->query->has('aid')) {
            $author = $authorRepository->find($request->query->get('aid'));
        }
        if ($this->isCsrfTokenValid('delete'.$book->getId(), $request->request->get('_token'))) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($book);
            $em->flush();
        }

        return $this->redirectToRoute('book_index', ['aid' => $author->getId()]);
    }
}
