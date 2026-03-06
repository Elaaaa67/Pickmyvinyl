<?php

namespace App\Controller\Admin;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use Doctrine\ORM\QueryBuilder;

class UserCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Utilisateur')
            ->setEntityLabelInPlural('Utilisateurs')
            ->setPageTitle('index', '👥 %entity_label_plural%')
            ->setPageTitle('new', 'Créer un %entity_label_singular%')
            ->setPageTitle('edit', 'Modifier l\'%entity_label_singular%')
            ->setPageTitle('detail', 'Détails de l\'%entity_label_singular%')
            ->setDefaultSort(['id' => 'DESC'])
            ->setPaginatorPageSize(30)
            ->setSearchFields(['email', 'Full_Name'])
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
                return $action->setIcon('fa fa-trash')->setLabel('Supprimer')
                    ->displayIf(function (User $user) {
                        // Empêcher la suppression de son propre compte
                        return $this->getUser() !== $user;
                    });
            })
            ->setPermission(Action::DELETE, 'ROLE_ADMIN');
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(BooleanFilter::new('isVerified', 'Email vérifié'))
            ->add(ChoiceFilter::new('roles', 'Rôle')->setChoices([
                'Administrateur' => 'ROLE_ADMIN',
                'Enseigne' => 'ROLE_STORE',
                'Utilisateur' => 'ROLE_USER',
            ]));
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id', 'N°')
                ->onlyOnIndex(),

            EmailField::new('email', 'Email')
                ->setHelp('Adresse email de connexion'),

            TextField::new('Full_Name', 'Nom complet')
                ->setHelp('Nom affiché de l\'utilisateur'),

            ChoiceField::new('roles', 'Rôles')
                ->setChoices([
                    'Administrateur' => 'ROLE_ADMIN',
                    'Enseigne/Magasin' => 'ROLE_STORE',
                    'Utilisateur' => 'ROLE_USER',
                ])
                ->allowMultipleChoices()
                ->renderExpanded()
                ->renderAsBadges([
                    'ROLE_ADMIN' => 'danger',
                    'ROLE_STORE' => 'info',
                    'ROLE_USER' => 'primary',
                ]),

            BooleanField::new('isVerified', 'Email vérifié')
                ->setHelp('L\'utilisateur a-t-il vérifié son adresse email?')
                ->renderAsSwitch(false)
                ->hideOnForm(), // Empêcher l'admin de modifier manuellement

            AssociationField::new('store', 'Enseigne associée')
                ->onlyOnDetail()
                ->formatValue(function ($value) {
                    return $value ? $value->getName() : 'Aucune';
                }),

            TextField::new('verificationToken', 'Token de vérification')
                ->onlyOnDetail()
                ->setHelp('Token unique pour vérifier l\'email (null si déjà vérifié)')
                ->formatValue(function ($value) {
                    return $value ? substr($value, 0, 20) . '...' : 'Vérifié ✓';
                }),
        ];
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $qb = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);

        // Filtre selon le paramètre 'type' envoyé depuis le menu
        $type = $this->container->get('request_stack')->getCurrentRequest()->query->get('type');

        if ($type === 'store') {
            $qb->andWhere('entity.roles LIKE :role')
                ->setParameter('role', '%"ROLE_STORE"%');
        } elseif ($type === 'user') {
            $qb->andWhere('entity.roles LIKE :role')->setParameter('role', '%"ROLE_USER"%')
                ->andWhere('entity.roles NOT LIKE :notRole')->setParameter('notRole', '%"ROLE_STORE"%');
        }

        return $qb;
    }
}
