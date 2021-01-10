<?php


namespace App\Controller;

use App\Entity\Post;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class PostController
 * @package App\Controller
 * @Route("/api", name="post_api")
 */
class PostController extends AbstractController
{
    /**
     * @param PostRepository $postRepository
     * @return JsonResponse
     * @Route("/posts", name="posts", methods={"GET"})
     */
    public function getPosts(PostRepository $postRepository){
        $data = $postRepository->findAll();
        return $this->response($data);
    }

    /**
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param PostRepository $postRepository
     * @return JsonResponse
     * @throws \Exception
     * @Route("/posts", name="posts_add", methods={"POST"})
     */
    public function addPost(Request $request, EntityManagerInterface $entityManager, PostRepository $postRepository){

        try{
            $request = $this->transformJsonBody($request);

            if (!$request || !$request->get('name') || !$request->request->get('description')){
                throw new \Exception();
            }

            $post = new Post();
            $post->setName($request->get('name'));
            $post->setDescription($request->get('description'));
            $post->setDateCreate(new \DateTime('now'));
            $entityManager->persist($post);
            $entityManager->flush();

            $data = [
                'status' => 200,
                'success' => "Post added successfully",
            ];
            return $this->response($data);

        }catch (\Exception $e){
            $data = [
                'status' => 422,
                'errors' => "Data no valid",
            ];
            return $this->response($data, 422);
        }

    }

    /**
     * @param PostRepository $postRepository
     * @param $id
     * @return JsonResponse
     * @Route("/posts/{id}", name="posts_get", methods={"GET"})
     */
    public function getPost(PostRepository $postRepository, $id){
        $post = $postRepository->find($id);

        if (!$post){
            $data = [
                'status' => 404,
                'errors' => "Post not found",
            ];
            return $this->response($data, 404);
        }
        return $this->response((array)$post);
    }

    /**
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param PostRepository $postRepository
     * @param $id
     * @return JsonResponse
     * @Route("/posts/{id}", name="posts_put", methods={"PUT"})
     */
    public function updatePost(Request $request, EntityManagerInterface $entityManager, PostRepository $postRepository, $id){

        try{
            $post = $postRepository->find($id);

            if (!$post){
                $data = [
                    'status' => 404,
                    'errors' => "Post not found",
                ];
                return $this->response($data, 404);
            }

            $request = $this->transformJsonBody($request);

            if (!$request || !$request->get('name') || !$request->request->get('description')){
                throw new \Exception();
            }

            $post->setName($request->get('name'));
            $post->setDescription($request->get('description'));
            $entityManager->flush();

            $data = [
                'status' => 200,
                'errors' => "Post updated successfully",
            ];
            return $this->response($data);

        }catch (\Exception $e){
            $data = [
                'status' => 422,
                'errors' => "Data no valid",
            ];
            return $this->response($data, 422);
        }

    }

    /**
     * @param EntityManagerInterface $entityManager
     * @param PostRepository $postRepository
     * @param $id
     * @return JsonResponse
     * @Route("/posts/{id}", name="posts_delete", methods={"DELETE"})
     */
    public function deletePost(EntityManagerInterface $entityManager, PostRepository $postRepository, $id){
        $post = $postRepository->find($id);

        if (!$post){
            $data = [
                'status' => 404,
                'errors' => "Post not found",
            ];
            return $this->response($data, 404);
        }

        $entityManager->remove($post);
        $entityManager->flush();
        $data = [
            'status' => 200,
            'errors' => "Post deleted successfully",
        ];
        return $this->response($data);
    }

    /**
     * @param array $data
     * @param int $status
     * @param array $headers
     * @return JsonResponse
     */
    public function response(array $data, $status = 200, $headers = [])
    {
        return new JsonResponse($data, $status, $headers);
    }

    protected function transformJsonBody(\Symfony\Component\HttpFoundation\Request $request)
    {
        $data = json_decode($request->getContent(), true);

        if ($data === null) {
            return $request;
        }

        $request->request->replace($data);

        return $request;
    }

}