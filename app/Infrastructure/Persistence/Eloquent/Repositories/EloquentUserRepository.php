<?php

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\User\Entities\User as UserEntity;
use App\Domain\User\Repositories\UserRepository;
use App\Domain\User\ValueObjects\Email;
use App\Infrastructure\Persistence\Eloquent\Models\User as UserModel;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;

class EloquentUserRepository implements UserRepository
{
    public function findById(int $id): ?UserEntity
    {
        $model = UserModel::with('roles')->find($id);

        return $model ? $this->toEntity($model) : null;
    }

    public function findByEmail(string $email): ?UserEntity
    {
        $model = UserModel::with('roles')->where('email', $email)->first();

        return $model ? $this->toEntity($model) : null;
    }

    public function findByEmployeeId(int $employeeId): ?UserEntity
    {
        $model = UserModel::with('roles')
            ->where('employee_id', $employeeId)
            ->first();
        return $model ? $this->toEntity($model) : null;
    }

    public function paginate(
        int $perPage,
        ?string $search = null,
        ?bool $onlyActive = null
    ): LengthAwarePaginator {
        $query = UserModel::query()
            ->select(['id', 'name', 'email', 'is_active', 'employee_id'])
            ->with(['roles:id,name']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if (! is_null($onlyActive)) {
            $query->where('is_active', $onlyActive);
        }

        $paginator = $query->paginate($perPage);

        $entities = $paginator->getCollection()
            ->map(fn (UserModel $model) => $this->toEntity($model));

        return new LengthAwarePaginator(
            items: $entities,
            total: $paginator->total(),
            perPage: $paginator->perPage(),
            currentPage: $paginator->currentPage(),
            options: [
                'path' => request()->url(),
                'query' => request()->query(),
            ],
        );
    }

    public function create(UserEntity $user, string $plainPassword): UserEntity
    {
        $model = new UserModel;
        $model->name = $user->name();
        $model->email = (string) $user->email();
        $model->is_active = $user->isActive();
        $model->password = Hash::make($plainPassword);
        $model->employee_id = $user->employeeId();
        $model->save();

        // set ID back to entity if you want
        return $this->toEntity($model->fresh('roles'));
    }

    public function update(UserEntity $user): UserEntity
    {
        $model = UserModel::findOrFail($user->id());
        $model->name = $user->name();
        $model->email = (string) $user->email();
        $model->is_active = $user->isActive();
        $model->employee_id = $user->employeeId();
        $model->save();

        return $this->toEntity($model->fresh('roles'));
    }

    public function delete(int $id): void
    {
        UserModel::findOrFail($id)->delete();
    }

    public function setRoles(UserEntity $user, array $roleNames): void
    {
        $model = UserModel::findOrFail($user->id());
        $model->syncRoles($roleNames);
    }

    public function changePassword(int $userId, string $plainPassword): void
    {
        $model = UserModel::findOrFail($userId);
        $model->password = Hash::make($plainPassword);
        $model->save();
    }

    private function toEntity(UserModel $model): UserEntity
    {
        return new UserEntity(
            id: $model->id,
            name: $model->name,
            email: new Email($model->email),
            active: $model->is_active,
            roles: $model->roles->pluck('name')->all(),
            employeeId: $model->employee_id,
        );
    }
}
