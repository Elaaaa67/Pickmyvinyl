<?php

namespace App\Controller\Admin;

use App\Entity\Vinyl;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\NumericFilter;

class VinylCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Vinyl::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Vinyle')
            ->setEntityLabelInPlural('Vinyles')
            ->setPageTitle('index', '🎵 %entity_label_plural%')
            ->setPageTitle('new', 'Ajouter un %entity_label_singular%')
            ->setPageTitle('edit', 'Modifier le %entity_label_singular%')
            ->setPageTitle('detail', 'Détails du %entity_label_singular%')
            ->setDefaultSort(['id' => 'DESC'])
            ->setPaginatorPageSize(30)
            ->setSearchFields(['title', 'artist', 'discogsId'])
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
            ->add(TextFilter::new('artist', 'Artiste'))
            ->add(TextFilter::new('title', 'Titre'))
            ->add(NumericFilter::new('year', 'Année'));
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id', 'N°')
                ->onlyOnIndex(),

            TextField::new('title', 'Titre')
                ->setHelp('Titre de l\'album'),

            TextField::new('artist', 'Artiste')
                ->setHelp('Nom de l\'artiste ou du groupe'),

            IntegerField::new('year', 'Année')
                ->setHelp('Année de sortie')
                ->hideOnIndex(),

            TextField::new('discogsId', 'ID Discogs')
                ->setHelp('Identifiant unique Discogs')
                ->onlyOnDetail(),

            ImageField::new('coverImage', 'Pochette')
                ->setBasePath('/')
                ->setUploadDir('public/images/vinyl')
                ->setUploadedFileNamePattern('[randomhash].[extension]')
                ->setHelp('Image de la pochette du vinyle')
                ->hideOnIndex(),

            TextField::new('coverImage', 'Image')
                ->onlyOnIndex()
                ->formatValue(function ($value) {
                    if (!$value) return '❌ Aucune';
                    return '<img src="' . $value . '" style="max-width: 50px; max-height: 50px; border-radius: 4px;"/>';
                })
                ->renderAsHtml(),

            TextareaField::new('description', 'Description')
                ->setHelp('Description optionnelle du vinyle')
                ->hideOnIndex()
                ->setRequired(false),
        ];
    }
}
