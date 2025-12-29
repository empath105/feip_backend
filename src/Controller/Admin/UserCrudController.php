<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;

/**
 * @extends AbstractCrudController<User>
 */
class UserCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Пользователь')
            ->setEntityLabelInPlural('Пользователи')
            ->setSearchFields(['name', 'email', 'phone'])
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->setPaginatorPageSize(20)
            ->showEntityActionsInlined();
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(TextFilter::new('name', 'Имя'))
            ->add(TextFilter::new('email', 'Email'))
            ->add(TextFilter::new('phone', 'Телефон'))
            ->add(DateTimeFilter::new('createdAt', 'Дата регистрации'));
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id', 'ID')->onlyOnIndex();
        yield TextField::new('name', 'Имя');
        yield EmailField::new('email', 'Email');
        yield TextField::new('phone', 'Телефон');
        yield ArrayField::new('roles', 'Роли')
            ->formatValue(function ($value) {
                return implode(', ', $value);
            });
        yield DateTimeField::new('createdAt', 'Дата регистрации')
            ->setFormat('Y-MM-dd HH:mm')
            ->hideOnForm();
        yield AssociationField::new('bookings', 'Бронирования')
            ->onlyOnIndex()
            ->formatValue(function ($value, $entity) {
                return count($entity->getBookings());
            });
    }
}
