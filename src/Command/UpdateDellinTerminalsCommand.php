<?php

namespace App\Command;

use App\Entity\DellinTerminal;
use App\Repository\DellinTerminalRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Команда по добавлению новых терминалов, если таковые появились посредством отправки запроса по апи в "Деловые Линии"
 */
#[AsCommand(
    name: 'app:dellin:update-terminals',
    description: 'Adding new dellin terminals every month if there are new ones',
)]
class UpdateDellinTerminalsCommand extends Command
{
    public function __construct(private KernelInterface $kernel,
                                private DellinTerminalRepository $dellinTerminalRep,
                                private EntityManagerInterface $em)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->text('Beginning to save terminals on database...');
        $terminalsFilePath = $this->kernel->getProjectDir().'/config/secrets/dellin_terminals.json';
        $terminalParentCities = json_decode(file_get_contents($terminalsFilePath), true)['city'];
        $newTerminalsAmount = 0;

        foreach ($terminalParentCities as $terminalCity) {
            $terminals = $terminalCity['terminals']['terminal'];
            foreach ($terminals as $terminal) {
                $terminalWithSuchId = $this->dellinTerminalRep->findOneBy(['terminal_id' => $terminal['id']]);
                if ($terminalWithSuchId !== null) {
                    $io->note("Terminal with terminal_id {$terminal['id']} already exists on database. Skipping.");
                    continue;
                }

                $newTerminalEntity = $this->prepareNewTerminalEntityByJson($terminal, $terminalCity['name'], $terminalCity['cityID']);
                $this->em->persist($newTerminalEntity);
                $newTerminalsAmount++;
            }
            $this->em->flush();
        }

        $io->success("Success! New saved terminals amount: $newTerminalsAmount");

        return Command::SUCCESS;
    }

    /**
     * Создание сущности DellinTerminal по образу массива $terminal для дальнейшего сохранения в БД
     * @param array $terminal
     * @param string $city
     * @param int $cityId
     * @return DellinTerminal
     */
    private function prepareNewTerminalEntityByJson(array $terminal, string $city, int $cityId): DellinTerminal
    {
        $newTerminalEntity = new DellinTerminal();

        $newTerminalEntity
            ->setCity($city)
            ->setCityId($cityId)
            ->setTerminalId($terminal['id'])
            ->setName($terminal['name'])
            ->setLatitude($terminal['latitude'])
            ->setLongitude($terminal['longitude'])
            ->setAddress($terminal['address'])
            ->setFullAddress($terminal['fullAddress']);

        if (isset($terminal['mainPhone']) && $terminal['mainPhone'])
            $newTerminalEntity->setPhone($terminal['mainPhone']);

        return $newTerminalEntity;
    }
}
