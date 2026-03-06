<?php

namespace App\Controller\Admin;

use App\Entity\Reservation;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;

class ReservationCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Reservation::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Réservation')
            ->setEntityLabelInPlural('Réservations')
            ->setPageTitle('index', '📋 %entity_label_plural%')
            ->setPageTitle('new', 'Créer une %entity_label_singular%')
            ->setPageTitle('edit', 'Modifier la %entity_label_singular%')
            ->setPageTitle('detail', 'Détails de la %entity_label_singular%')
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->setPaginatorPageSize(25)
            ->setSearchFields(['id', 'client.email', 'vinyl.title', 'store.name', 'status'])
            ->showEntityActionsInlined();
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->update(Crud::PAGE_INDEX, Action::DETAIL, function (Action $action) {
                return $action->setIcon('fa fa-eye')->setLabel('Voir');
            })
            ->update(Crud::PAGE_INDEX, Action::EDIT, function (Action $action) {
                return $action->setIcon('fa fa-edit')->setLabel('Modifier');
            })
            ->update(Crud::PAGE_INDEX, Action::DELETE, function (Action $action) {
                return $action->setIcon('fa fa-trash')->setLabel('Supprimer');
            })
            ->disable(Action::NEW); // Empêcher la création manuelle depuis l'admin
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(EntityFilter::new('client', 'Client'))
            ->add(EntityFilter::new('store', 'Magasin'))
            ->add(ChoiceFilter::new('status', 'Statut')->setChoices([
                'En attente' => 'pending',
                'Confirmée' => 'confirmed',
                'Annulée' => 'cancelled',
                'Rejetée' => 'rejected',
            ]))
            ->add(DateTimeFilter::new('createdAt', 'Date de création'));
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id', 'N°')
                ->onlyOnIndex(),

            AssociationField::new('client', 'Client')
                ->formatValue(function ($value, $entity) {
                    return $value ? $value->getEmail() : 'N/A';
                })
                ->setCssClass('text-primary'),

            AssociationField::new('vinyl', 'Vinyle')
                ->formatValue(function ($value, $entity) {
                    if (!$value) return 'N/A';
                    $artist = $value->getArtist() ? ' - ' . $value->getArtist() : '';
                    return '<strong>' . $value->getTitle() . '</strong>' . $artist;
                })
                ->renderAsHtml(),

            AssociationField::new('store', 'Magasin')
                ->formatValue(function ($value, $entity) {
                    if (!$value) return 'N/A';
                    $address = $value->getAddress() ? ' <small>(' . $value->getAddress() . ')</small>' : '';
                    return $value->getName() . $address;
                })
                ->renderAsHtml(),

            IntegerField::new('quantity', 'Qté')
                ->setHelp('Nombre d\'exemplaires réservés'),

            ChoiceField::new('status', 'Statut')
                ->setChoices([
                    'En attente' => 'pending',
                    'Confirmée' => 'confirmed',
                    'Annulée' => 'cancelled',
                    'Rejetée' => 'rejected',
                ])
                ->renderAsBadges([
                    'pending' => 'warning',
                    'confirmed' => 'success',
                    'cancelled' => 'danger',
                    'rejected' => 'secondary',
                ])
                ->hideOnForm(),

            TextField::new('status', 'Statut')
                ->onlyOnForms()
                ->setHelp('Statut actuel de la réservation'),

            DateTimeField::new('createdAt', 'Créée le')
                ->setFormat('dd/MM/yyyy HH:mm')
                ->onlyOnIndex(),

            DateTimeField::new('createdAt', 'Date de création')
                ->setFormat('dd/MM/yyyy à HH:mm:ss')
                ->onlyOnDetail()
                ->setHelp('Date et heure de création de la réservation'),
        ];
    }
}

