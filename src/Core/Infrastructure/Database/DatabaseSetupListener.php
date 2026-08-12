<?php

namespace App\Core\Infrastructure\Database;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Listener que verifica e cria o banco de dados automaticamente na inicialização
 */
class DatabaseSetupListener implements EventSubscriberInterface
{
    private bool $setupExecuted = false;

    public function __construct(
        private readonly Connection $connection,
        private readonly ?LoggerInterface $logger = null
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 1000],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        // Executar apenas uma vez por requisição
        if ($this->setupExecuted) {
            return;
        }

        $this->setupExecuted = true;

        // Apenas executar em ambiente de desenvolvimento
        // Em produção, o banco deve estar configurado manualmente
        if ($_ENV['APP_ENV'] ?? 'dev' === 'prod') {
            return;
        }

        try {
            $this->ensureDatabaseExists();
        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->warning('Não foi possível configurar banco de dados automaticamente: ' . $e->getMessage());
            }
            // Não interromper a requisição se houver erro
        }
    }

    /**
     * Verifica se o banco de dados existe e cria se necessário
     */
    private function ensureDatabaseExists(): void
    {
        try {
            // Tentar conectar ao banco de dados
            $this->connection->connect();
            
            // Se chegou aqui, o banco existe e está acessível
            if ($this->logger) {
                $this->logger->debug('Banco de dados verificado e acessível.');
            }
            return;
        } catch (Exception $e) {
            // Se a conexão falhar, pode ser que o banco não exista
            // Verificar se é erro de banco não encontrado
            $errorMessage = $e->getMessage();
            if (str_contains($errorMessage, 'does not exist') || 
                str_contains($errorMessage, 'database') && str_contains($errorMessage, 'not found')) {
                $this->createDatabaseIfNotExists();
            } else {
                // Outro tipo de erro (servidor não disponível, etc)
                throw $e;
            }
        }
    }

    /**
     * Cria o banco de dados se não existir
     */
    private function createDatabaseIfNotExists(): void
    {
        $params = $this->connection->getParams();
        $dbName = $params['dbname'] ?? $params['path'] ?? null;

        if (!$dbName) {
            return;
        }

        // Criar conexão sem especificar o banco de dados
        $serverParams = $params;
        unset($serverParams['dbname']);

        try {
            $serverConnection = new \PDO(
                sprintf(
                    'pgsql:host=%s;port=%s',
                    $serverParams['host'] ?? 'localhost',
                    $serverParams['port'] ?? 5432
                ),
                $serverParams['user'] ?? 'postgres',
                $serverParams['password'] ?? 'postgres'
            );

            // Verificar se o banco já existe
            $stmt = $serverConnection->query(
                "SELECT 1 FROM pg_database WHERE datname = " . $serverConnection->quote($dbName)
            );

            if ($stmt->rowCount() === 0) {
                // Criar o banco de dados
                // PostgreSQL não aceita aspas simples em CREATE DATABASE
                // Para nomes simples (letras, números, underscore), não precisa de aspas
                // Para nomes com caracteres especiais, usar aspas duplas
                $escapedDbName = preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $dbName) 
                    ? $dbName 
                    : '"' . str_replace('"', '""', $dbName) . '"';
                $serverConnection->exec("CREATE DATABASE " . $escapedDbName);
                
                if ($this->logger) {
                    $this->logger->info("Banco de dados '{$dbName}' criado com sucesso.");
                }
            }

            // Reconectar ao banco criado
            $this->connection->connect();
        } catch (\PDOException $e) {
            if ($this->logger) {
                $this->logger->error("Erro ao criar banco de dados: " . $e->getMessage());
            }
            throw $e;
        }
    }
}

