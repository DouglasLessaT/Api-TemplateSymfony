<?php

/**
 * Script para configurar o banco de dados automaticamente
 * 
 * Este script verifica se o banco de dados existe e cria se necessário.
 * Pode ser executado manualmente ou chamado pelo start-dev.sh
 */

$configPath = dirname(__DIR__) . '/config.php';

if (!file_exists($configPath)) {
    echo "❌ Arquivo config.php não encontrado!\n";
    exit(1);
}

$config = require $configPath;
$dbConfig = $config['database'] ?? null;

if (!$dbConfig) {
    echo "❌ Configuração de banco de dados não encontrada em config.php!\n";
    exit(1);
}

$dbName = $dbConfig['database'] ?? 'oracle_tgc';
$host = $dbConfig['host'] ?? 'localhost';
$port = $dbConfig['port'] ?? 3306;
$username = $dbConfig['username'] ?? 'root';
$password = $dbConfig['password'] ?? 'root';

echo "🗄️  Configurando banco de dados...\n";
echo "   Host: {$host}:{$port}\n";
echo "   Database: {$dbName}\n";
echo "   User: {$username}\n\n";

try {
    // Tentar conectar ao banco de dados
    $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', $host, $port, $dbName);
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
        echo "✅ Banco de dados '{$dbName}' já existe e está acessível.\n";
        
        // Verificar se as tabelas existem
        echo "\n📋 Verificando tabelas...\n";
        createTables($dsn, $username, $password);
        
        exit(0);
} catch (PDOException $e) {
    // Se falhar, pode ser que o banco não exista
    $errorMessage = $e->getMessage();
    
    if (str_contains($errorMessage, 'Unknown database') || 
        str_contains($errorMessage, 'database') && str_contains($errorMessage, 'not exist')) {
        
        echo "⚠️  Banco de dados não encontrado. Criando...\n";
        
        try {
            // Conectar ao servidor MySQL (sem banco específico)
            $serverDsn = sprintf('mysql:host=%s;port=%s;charset=utf8mb4', $host, $port);
            $serverPdo = new PDO($serverDsn, $username, $password);
            $serverPdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Verificar se o banco já existe
            $stmt = $serverPdo->query(
                "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = " . $serverPdo->quote($dbName)
            );
            
            if ($stmt->rowCount() > 0) {
                echo "ℹ️  Banco de dados '{$dbName}' já existe no servidor.\n";
                echo "   Verifique as credenciais ou permissões.\n";
                exit(1);
            }
            
            // Criar o banco de dados
            $escapedDbName = "`" . str_replace("`", "``", $dbName) . "`";
            $serverPdo->exec("CREATE DATABASE " . $escapedDbName . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            echo "✅ Banco de dados '{$dbName}' criado com sucesso!\n";
            
            // Tentar conectar novamente para confirmar
            $pdo = new PDO($dsn, $username, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            echo "✅ Conexão confirmada!\n";
            
            // Criar tabelas usando Doctrine se disponível
            echo "\n📋 Criando tabelas das entidades...\n";
            createTables($dsn, $username, $password);
            
            exit(0);
        } catch (PDOException $e2) {
            echo "❌ Erro ao criar banco de dados: " . $e2->getMessage() . "\n";
            echo "\n";
            echo "💡 Dicas:\n";
            echo "   - Verifique se o MySQL está rodando\n";
            echo "   - Verifique as credenciais em config.php\n";
            echo "   - Verifique se o usuário tem permissão para criar bancos\n";
            exit(1);
        }
    } else {
        // Outro tipo de erro
        echo "❌ Erro ao conectar ao banco de dados: {$errorMessage}\n";
        echo "\n";
        echo "💡 Dicas:\n";
        echo "   - Verifique se o MySQL está rodando\n";
        echo "   - Verifique as credenciais em config.php\n";
        echo "   - Verifique se a porta {$port} está correta\n";
        exit(1);
    }
}

/**
 * Cria as tabelas usando Doctrine ORM
 */
function createTables(string $dsn, string $username, string $password): void
{
    $vendorPath = dirname(__DIR__) . '/vendor/autoload.php';
    
    if (!file_exists($vendorPath)) {
        echo "⚠️  vendor/autoload.php não encontrado. Execute 'composer install' primeiro.\n";
        echo "   As tabelas não serão criadas automaticamente.\n";
        return;
    }
    
    try {
        require_once $vendorPath;
        
        // Verificar se Doctrine está disponível
        if (!class_exists('Doctrine\DBAL\DriverManager')) {
            echo "⚠️  Doctrine DBAL não encontrado. Instale as dependências com 'composer install'.\n";
            return;
        }
        
        // Extrair informações do DSN (simplificado para MySQL)
        // mysql:host=localhost;port=3306;dbname=oracle_tgc;charset=utf8mb4
        $dbHost = 'localhost';
        $dbPort = 3306;
        $dbName = 'oracle_tgc';
        
        if (preg_match('/host=([^;]+)/', $dsn, $matches)) $dbHost = $matches[1];
        if (preg_match('/port=([^;]+)/', $dsn, $matches)) $dbPort = $matches[1];
        if (preg_match('/dbname=([^;]+)/', $dsn, $matches)) $dbName = $matches[1];
        
        // Criar conexão Doctrine
        $connectionParams = [
            'driver' => 'pdo_mysql',
            'host' => $dbHost,
            'port' => $dbPort,
            'dbname' => $dbName,
            'user' => $username,
            'password' => $password,
            'charset' => 'utf8mb4',
        ];
        
        $connection = \Doctrine\DBAL\DriverManager::getConnection($connectionParams);
        
        // Verificar se já existem tabelas
        $schemaManager = $connection->createSchemaManager();
        $tables = $schemaManager->listTableNames();
        
        if (count($tables) > 0) {
            echo "ℹ️  Tabelas já existem (" . count($tables) . " tabelas encontradas).\n";
            return;
        }
        
        // Tentar usar Symfony Console primeiro (melhor opção)
        $consolePath = dirname(__DIR__) . '/bin/console';
        $projectDir = dirname(__DIR__);
        
        // Se não existir bin/console, tentar criar ou usar alternativa
        if (!file_exists($consolePath)) {
            // Tentar criar bin/console se possível
            $consoleContent = <<<'PHP'
#!/usr/bin/env php
<?php

use App\Kernel;
use Symfony\Bundle\FrameworkBundle\Console\Application;

if (!file_exists(dirname(__DIR__).'/vendor/autoload_runtime.php')) {
    throw new LogicException('Symfony Runtime is missing. Try running "composer require symfony/runtime".');
}

require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

return function (array $context) {
    $kernel = new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
    
    return new Application($kernel);
};
PHP;
            @file_put_contents($consolePath, $consoleContent);
            @chmod($consolePath, 0755);
        }
        
        if (file_exists($consolePath)) {
            echo "   Usando Symfony Console para criar schema...\n";
            $output = [];
            $returnVar = 0;
            $command = "cd {$projectDir} && php {$consolePath} doctrine:schema:create --no-interaction 2>&1";
            exec($command, $output, $returnVar);
            
            if ($returnVar === 0) {
                echo "✅ Tabelas criadas com sucesso via Symfony Console!\n";
                return;
            } else {
                $errorOutput = implode("\n", array_slice($output, -5)); // últimas 5 linhas
                echo "⚠️  Erro ao executar Symfony Console.\n";
                if (!empty($errorOutput)) {
                    echo "   Erro: {$errorOutput}\n";
                }
                echo "   Tentando método alternativo...\n";
            }
        }
        
        // Tentar usar Doctrine ORM diretamente
        if (class_exists('Doctrine\ORM\EntityManager')) {
            echo "   Tentando usar Doctrine ORM diretamente...\n";
            
            // Configurar Doctrine ORM
            $config = \Doctrine\ORM\Tools\Setup::createAttributeMetadataConfiguration(
                [dirname(__DIR__) . '/src/Domain/Entity'],
                true // modo desenvolvimento
            );
            
            $entityManager = \Doctrine\ORM\EntityManager::create($connectionParams, $config);
            
            // Criar schema
            $tool = new \Doctrine\ORM\Tools\SchemaTool($entityManager);
            $classes = $entityManager->getMetadataFactory()->getAllMetadata();
            
            if (empty($classes)) {
                echo "⚠️  Nenhuma entidade Doctrine encontrada.\n";
                echo "   As entidades precisam ter anotações Doctrine (@Entity, #[Entity], etc).\n";
                echo "\n";
                echo "💡 Para criar as tabelas manualmente:\n";
                echo "   1. Adicione anotações Doctrine às entidades, ou\n";
                echo "   2. Execute: php bin/console doctrine:schema:create\n";
                echo "   3. Ou crie as tabelas manualmente via SQL\n";
                return;
            }
            
            $tool->createSchema($classes);
            echo "✅ Tabelas criadas com sucesso via Doctrine ORM!\n";
        } else {
            echo "⚠️  Doctrine ORM não encontrado.\n";
            echo "\n";
            echo "💡 Para criar as tabelas:\n";
            echo "   1. Execute: php bin/console doctrine:schema:create\n";
            echo "   2. Ou: php bin/console doctrine:migrations:migrate\n";
            echo "   3. Ou crie as tabelas manualmente via SQL\n";
        }
    } catch (\Exception $e) {
        echo "⚠️  Erro ao criar tabelas: " . $e->getMessage() . "\n";
        echo "   Você pode criar as tabelas manualmente com:\n";
        echo "   php bin/console doctrine:schema:create\n";
        echo "   ou\n";
        echo "   php bin/console doctrine:migrations:migrate\n";
    }
}

