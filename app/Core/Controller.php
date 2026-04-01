<?php
/**
 * SIGR - Base Controller
 */

namespace Core;

abstract class Controller
{
    /**
     * Rendre une vue (wrapper pour Helpers)
     */
    protected function render(string $view, array $data = []): void
    {
        Helpers::render($view, $data);
    }
    
    /**
     * Rediriger vers une URL (wrapper)
     */
    protected function redirect(string $url): never
    {
        header('Location: ' . Helpers::url($url));
        exit;
    }
    
    /**
     * Récupérer les données POST en JSON
     */
    protected function getJsonInput(): array
    {
        return Helpers::getJsonInput();
    }
    
    /**
     * Vérifier si l'utilisateur staff est connecté
     */
    protected function requireAuth(): void
    {
        $session = Session::getInstance();
        if (!$session->isStaffLoggedIn()) {
            $this->redirect('admin/login');
        }
    }
}
