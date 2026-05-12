<?php
namespace App\Apis;

use App\Domain\Category;
use App\Infrastructure\Repository\CategoryRepository;
use WebFiori\Database\Database;
use WebFiori\Framework\App;
use WebFiori\Framework\Session\SessionsManager;
use WebFiori\Http\Annotations\GetMapping;
use WebFiori\Http\Annotations\PostMapping;
use WebFiori\Http\Annotations\RequestParam;
use WebFiori\Http\Annotations\ResponseBody;
use WebFiori\Http\Annotations\RestController;
use WebFiori\Http\ParamType;
use WebFiori\Http\WebService;

#[RestController('categories', 'Categories — list and create transaction categories.')]
class CategoryService extends WebService {
    #[PostMapping]
    #[ResponseBody]
    #[RequestParam(name: 'name', type: ParamType::STRING, description: 'Category name.')]
    #[RequestParam(name: 'type', type: ParamType::STRING, optional: true, default: 'expense', description: 'Type: income or expense.')]
    #[RequestParam(name: 'icon', type: ParamType::STRING, optional: true, default: '', description: 'Icon identifier.')]
    #[RequestParam(name: 'color', type: ParamType::STRING, optional: true, default: '#333333', description: 'Hex color code.')]
    public function createCategory(?string $name = null, ?string $type = null, ?string $icon = null, ?string $color = null): array {
        $cat = new Category(userId: $this->getUserId(), name: $name, type: $type ?? 'expense', icon: $icon ?? '', color: $color ?? '#333333');
        $this->getRepo()->save($cat);

        return [$cat];
    }

    #[GetMapping]
    #[ResponseBody]
    public function getCategories(): array {
        return $this->getRepo()->findByUserId($this->getUserId());
    }
    public function isAuthorized(): bool {
        SessionsManager::start('wf-session');

        return SessionsManager::get('user-id') !== null;
    }

    private function getRepo(): CategoryRepository {
        return new CategoryRepository(new Database(App::getConfig()->getDBConnection('finance')));
    }

    private function getUserId(): int {
        return (int) SessionsManager::get('user-id');
    }
}
