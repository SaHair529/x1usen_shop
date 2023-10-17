<?php

namespace App\Command;

use App\Entity\DellinPlace;
use App\Repository\DellinPlaceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Команда по добавлению новых населенных пунктов, если таковые появились посредством отправки запроса по апи в "Деловые Линии"
 */
#[AsCommand(
    name: 'app:dellin:update-places',
    description: 'Adding new dellin places every month if there are new ones',
)]
class UpdateDellinPlacesCommand extends Command
{
    public function __construct(private KernelInterface $kernel,
                                private DellinPlaceRepository $dellinPlaceRep,
                                private EntityManagerInterface $em)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->text('Beginning to save places on database...');
        $placesFilePath = $this->kernel->getProjectDir().'/config/secrets/dellin_places.csv';
        $newPlacesAmount = 0;

        $placesFileStream = fopen($placesFilePath, 'r');

        $csvRowMap = array_flip(fgetcsv($placesFileStream));

        while(($placeCsvRow = fgetcsv($placesFileStream)) !== false) {
            $placeWithSuchCityId = $this->dellinPlaceRep->findOneBy(['city_id' => $placeCsvRow[$csvRowMap['cityID']]]);
            if ($placeWithSuchCityId !== null) {
                $io->note("Place with city_id {$placeCsvRow[$csvRowMap['cityID']]} already exists on database. Skipping.");
                continue;
            }

            $io->text("Persisted places amount: $newPlacesAmount");

            $newPlaceEntity = $this->prepareNewPlaceEntityByCsvRow($placeCsvRow, $csvRowMap);
            $this->em->persist($newPlaceEntity);
            $newPlacesAmount++;
        }

        fclose($placesFileStream);

        $this->em->flush();
        $io->success("Success! New saved places amount: $newPlacesAmount");

        return Command::SUCCESS;
    }

    /**
     * Создание сущности @link DellinPlace по образу массива $placeCsvRow для дальнейшего сохранения в БД
     */
    private function prepareNewPlaceEntityByCsvRow(array $placeCsvRow, array $csvRowMap): DellinPlace
    {
        $newPlaceEntity = new DellinPlace();

        $newPlaceEntity
            ->setCityId($placeCsvRow[$csvRowMap['cityID']])
            ->setName($placeCsvRow[$csvRowMap['name']])
            ->setCode($placeCsvRow[$csvRowMap['code']])
            ->setSearchString($placeCsvRow[$csvRowMap['searchString']])
            ->setRegion($placeCsvRow[$csvRowMap['regname']])
            ->setRegionCode($placeCsvRow[$csvRowMap['regcode']])
            ->setZoneName($placeCsvRow[$csvRowMap['zonname']])
            ->setZoneCode($placeCsvRow[$csvRowMap['zoncode']]);

        return $newPlaceEntity;
    }
}
