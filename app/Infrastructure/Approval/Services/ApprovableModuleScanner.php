<?php

namespace App\Infrastructure\Approval\Services;

use App\Domain\Approval\Contracts\Approvable;
use Illuminate\Support\Facades\File;
use ReflectionClass;

class ApprovableModuleScanner
{
    /**
     * Scan the application to find all classes implementing the Approvable interface.
     *
     * @return array<string, string> Map of class name to human-readable label
     */
    public function scan(): array
    {
        $modules = [];
        $paths = [
            app_path('Domain'),
            app_path('Models'),
        ];

        foreach ($paths as $path) {
            $files = File::allFiles($path);

            foreach ($files as $file) {
                if ($file->getExtension() !== 'php') {
                    continue;
                }

                $className = $this->getClassNameFromFile($file->getRealPath());

                if (! $className || ! class_exists($className)) {
                    continue;
                }

                $reflection = new ReflectionClass($className);

                if ($reflection->isInstantiable() && $reflection->implementsInterface(Approvable::class)) {
                    try {
                        $instance = $reflection->newInstanceWithoutConstructor();
                        $label = $instance->getApprovableTypeLabel();
                        $modules[$className] = $label;
                    } catch (\Throwable $e) {
                        // Skip if cannot instantiate or call method
                        continue;
                    }
                }
            }
        }

        return $modules;
    }

    /**
     * Extract full class name (with namespace) from a file path.
     */
    private function getClassNameFromFile(string $filePath): ?string
    {
        $content = file_get_contents($filePath);
        $namespace = null;
        $class = null;

        if (preg_match('/namespace\s+([^;]+);/', $content, $matches)) {
            $namespace = trim($matches[1]);
        }

        if (preg_match('/class\s+(\w+)/', $content, $matches)) {
            $class = trim($matches[1]);
        }

        return $namespace && $class ? "{$namespace}\\{$class}" : null;
    }
}
