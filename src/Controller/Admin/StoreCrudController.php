<?php

namespace App\Controller\Admin;

use App\Entity\Store;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;

class StoreCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Store::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Magasin')
            ->setEntityLabelInPlural('Magasins')
            ->setPageTitle('index', '🏪 %entity_label_plural%')
            ->setPageTitle('new', 'Créer un %entity_label_singular%')
            ->setPageTitle('edit', 'Modifier le %entity_label_singular%')
            ->setPageTitle('detail', 'Détails du %entity_label_singular%')
            ->setDefaultSort(['id' => 'DESC'])
            ->setPaginatorPageSize(25)
            ->setSearchFields(['name', 'address', 'phone'])
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
            ->update(Crud::PAGE_INDEX, Action::NEW, function (Action $action) {
                return $action->setIcon('fa fa-plus')->setLabel('Ajouter');
            });
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(TextFilter::new('name', 'Nom'))
            ->add(TextFilter::new('address', 'Adresse'))
            ->add(EntityFilter::new('owner', 'Propriétaire'));
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id', 'N°')
                ->onlyOnIndex(),

            TextField::new('name', 'Nom du magasin')
                ->setHelp('Nom commercial du magasin'),

            TextareaField::new('address', 'Adresse')
                ->setHelp('Adresse complète du magasin')
                ->hideOnIndex(),

            TextField::new('address', 'Adresse')
                ->onlyOnIndex()
                ->formatValue(function ($value) {
                    return $value ? substr($value, 0, 50) . '...' : 'N/A';
                }),

            TextField::new('phone', 'Téléphone')
                ->setHelp('Numéro de téléphone du magasin'),

            AssociationField::new('owner', 'Propriétaire')
                ->setHelp('Compte utilisateur associé à ce magasin')
                ->formatValue(function ($value) {
                    if (!$value) return '<span class="badge badge-warning">Aucun</span>';
                    return '<strong>' . $value->getEmail() . '</strong>';
                })
                ->renderAsHtml(),
        ];
    }
}

