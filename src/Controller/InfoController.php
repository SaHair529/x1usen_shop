<?php

namespace App\Controller;

use App\Service\DataMapping;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/info')]
class InfoController extends AbstractController
{
    #[Route('/about_us', name: 'about_us_page')]
    public function aboutUsPage(DataMapping $dataMapping): Response
    {


        return $this->render('info/about_us.html.twig', [
            'company_inn' => $dataMapping->getData('companyINN'),
            'company_kpp' => $dataMapping->getData('companyKPP'),
            'company_ogrn' => $dataMapping->getData('companyOGRN'),

            'company_checking_account' => $dataMapping->getData('companyCheckingAccount'),
            'company_bank' => $dataMapping->getData('companyBank'),
            'company_bik' => $dataMapping->getData('companyBIK'),
            'company_corporation_account' => $dataMapping->getData('companyCorporationAccount'),

            'company_juridical_address' => $dataMapping->getData('companyJuridicalAddress'),
            'company_contact_phone' => $dataMapping->getData('companyContactPhone'),
            'company_owner_fullname' => $dataMapping->getData('companyOwnerFullname'),
        ]);
    }

    #[Route('/delivery', name: 'delivery_page')]
    public function deliveryPage(DataMapping $dataMapping): Response
    {
        return $this->render('info/delivery.html.twig', [
            'company_manager_contact_phone' => $dataMapping->getData('companyManagerContactPhone')
        ]);
    }
}