<?php

namespace Tengliyun\Token\Console;

use Tengliyun\Token\Token;
use GmTLS\CryptoKit\RSA;
use Illuminate\Console\Command;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputOption;
use Throwable;

#[AsCommand(name: 'token:keys')]
class KeysCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'token:keys';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create the encryption keys for API authentication';

    /**
     * Execute the console command.
     *
     * @return bool
     */
    public function handle(): bool
    {
        $publicKey  = Token::keyPath('token-public.key');
        $privateKey = Token::keyPath('token-private.key');

        if (file_exists($publicKey) || file_exists($privateKey)) {
            if ($this->option('force') === false) {
                $this->components->error('Encryption keys already exist. Use the --force option to overwrite them.');
                return false;
            }
        }

        try {
            $key = RSA::createKey($this->hasOption('length') ? (int) $this->option('length') : 4096);
        } catch (Throwable $throwable) {
            $this->components->error($throwable->getMessage());
            return false;
        }

        if ($this->storage($publicKey, $key->getPublicKey()) === false) {
            $this->components->error("Failed to write file: {$publicKey}");
            return false;
        }

        if ($this->storage($privateKey, $key->getPrivateKey()) === false) {
            $this->components->error("Failed to write file: {$privateKey}");
            return false;
        }

        if (!windows_os()) {
            chmod($publicKey, 0660);
            chmod($privateKey, 0600);
        }

        $this->components->info('Encryption keys generated successfully.');
        return true;
    }

    /**
     * Put contents to a file safely.
     *
     * @param string $path
     * @param string $data
     * @param int    $flags
     *
     * @return bool|int
     */
    protected function storage(string $path, string $data, int $flags = 0): bool|int
    {
        $dir = dirname($path);

        if (!is_dir($dir) && !mkdir($dir, 0777, true) && !is_dir($dir)) {
            $this->components->error("Failed to create directory: {$dir}");
            return false;
        }

        return file_put_contents($path, $data, $flags);
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions(): array
    {
        return [
            ['force', 'f', InputOption::VALUE_NONE, 'Overwrite keys they already exist.'],
            ['length', null, InputOption::VALUE_REQUIRED, 'The length of the private key.', 4096],
        ];
    }
}
