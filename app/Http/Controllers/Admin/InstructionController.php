<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\View\View;

class InstructionController extends Controller
{
    /**
     * Список статей инструкции (две отдельные статьи).
     */
    public function index(): View
    {
        $path = base_path('docs/ADMIN_INSTRUCTION.md');
        $content = File::exists($path)
            ? File::get($path)
            : "# Инструкции\n\nСтатьи не найдены.";

        $html = Str::markdown($content);
        // Превратить относительные ссылки в маршруты админки
        $html = preg_replace_callback(
            '~href="(steps|setup)"~',
            fn (array $m) => 'href="' . url('/admin/instruction/' . $m[1]) . '"',
            $html
        );

        return view('admin.instruction.index', [
            'content' => $html,
            'title'   => 'Инструкции для администратора',
            'subtitle' => 'Выберите статью',
        ]);
    }

    /**
     * Пошаговая инструкция для администратора GND Admin.
     */
    public function steps(): View
    {
        $path = base_path('docs/ADMIN_INSTRUCTION_STEPS.md');
        $content = File::exists($path)
            ? File::get($path)
            : "# Статья не найдена\n\nФайл `docs/ADMIN_INSTRUCTION_STEPS.md` отсутствует.";

        return $this->articleView($content, 'Пошаговая инструкция для администратора GND Admin', 'Первичная настройка и ежедневная работа');
    }

    /**
     * Сводная настройка: нода, админка, кошелёк и native_contracts.json.
     */
    public function setup(): View
    {
        $path = base_path('docs/ADMIN_INSTRUCTION_SETUP.md');
        $content = File::exists($path)
            ? File::get($path)
            : "# Статья не найдена\n\nФайл `docs/ADMIN_INSTRUCTION_SETUP.md` отсутствует.";

        return $this->articleView($content, 'Сводная настройка: нода, админка, кошелёк и native_contracts.json', 'Связка ноды, админки и конфига');
    }

    private function articleView(string $markdown, string $title, string $subtitle): View
    {
        $html = Str::markdown($markdown);

        return view('admin.instruction.show', [
            'content'  => $html,
            'title'   => $title,
            'subtitle' => $subtitle,
        ]);
    }
}
