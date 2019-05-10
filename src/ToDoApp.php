<?php declare(strict_types=1);

namespace Acme;

use Closure;
use League\Route\Http;
use League\Route\Router;
use Middlewares\JsonPayload;
use PDO;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Ramsey\Uuid\Uuid;
use Throwable;
use Zend\Diactoros\Response\JsonResponse;

class ToDoApp implements RequestHandler
{
    /**
     * @var Router
     */
    private $router;

    /**
     * @var ToDoRepository
     */
    public $toDoRepo;

    public function __construct(Router $router, ToDoRepository $toDoRepo)
    {
        $router->map('GET', '/', Closure::fromCallable([ $this, 'handleGetList']));
        $router->map('GET', '/{id}', Closure::fromCallable([ $this, 'handleGet']));
        $router->map('PUT', '/{id}', Closure::fromCallable([ $this, 'handleUpsert']));

        $this->router = $router;
        $this->toDoRepo = $toDoRepo;
    }

    public static function bootstrap(): self
    {
        $router = new Router();
        $router->middleware(new JsonPayload());

        $dsn = sprintf(
            'pgsql:host=%s;port=%d;dbname=%s',
            getenv('DB_HOST'),
            getenv('DB_PORT'),
            getenv('DB_NAME')
        );

        $pdo = new PDO($dsn, getenv('DB_USER'), getenv('DB_PASSWORD'));
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $toDoRepo = new ToDoRepository($pdo);

        return new self($router, $toDoRepo);
    }

    public function handle(Request $request): Response
    {
        try {
            $response = $this->router->dispatch($request);
        } catch (Http\Exception $e) {
            return new JsonResponse([ 'error' => $e->getMessage()], $e->getStatusCode());
        } catch (Throwable $e) {
            return new JsonResponse([ 'error' => $e->getMessage(), 'trace' => $e->getTrace()], 500);
        }

        $response = $response->withHeader('X-Foo', 'bar');

        return $response;
    }

    /**
     * @throws Http\Exception
     */
    private function handleGet(Request $request, array $args)
    {
        $id = $this->marshallIdParam($args);

        $item = $this->toDoRepo->find($id);

        if ($item === null) {
            throw new Http\Exception\NotFoundException();
        }

        return new JsonResponse($item);
    }

    private function handleGetList()
    {
        return new JsonResponse($this->toDoRepo->getAll());
    }

    /**
     * @throws Http\Exception
     */
    private function handleUpsert(Request $request, array $args): Response
    {
        $requestBody = $request->getParsedBody();

        if (! isset($requestBody['content'])) {
            throw new Http\Exception\BadRequestException('Missing content');
        }

        $content = trim($requestBody['content']);

        if ($content === '') {
            throw new Http\Exception\BadRequestException('Empty content');
        }

        $id = $this->marshallIdParam($args);

        $item = $this->toDoRepo->find($id);

        if ($item === null) {
            $item = new ToDo($content, $id);
            $this->toDoRepo->add($item);
        } else {
            $item->setContent($content);
            $this->toDoRepo->update($item);
        }

        return new JsonResponse($item);
    }

    private function marshallIdParam(array $args): string
    {
        $id = $args['id'];

        if (! Uuid::isValid($id)) {
            throw new Http\Exception\BadRequestException('Invalid UUID');
        }

        return $id;
    }
}
