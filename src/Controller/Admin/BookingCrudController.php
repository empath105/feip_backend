<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Booking;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;

/**
 * @extends AbstractCrudController<Booking>
 */
class BookingCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Booking::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Бронирование')
            ->setEntityLabelInPlural('Бронирования')
            ->setSearchFields(['comment', 'status'])
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->setPaginatorPageSize(20)
            ->showEntityActionsInlined();
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(EntityFilter::new('user', 'Пользователь'))
            ->add(EntityFilter::new('house', 'Дом'))
            ->add(ChoiceFilter::new('status', 'Статус')->setChoices([
                'active' => 'Активно',
                'cancelled' => 'Отменено',
                'completed' => 'Завершено',
            ]))
            ->add(DateTimeFilter::new('createdAt', 'Дата создания'));
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id', 'ID')->onlyOnIndex();
        yield AssociationField::new('user', 'Пользователь (ID)')
            ->formatValue(function ($value, $entity) {
                return $entity->getUser() ? '#' . $entity->getUser()->getId() : '—';
            });
        yield AssociationField::new('house', 'Дом (ID)')
            ->formatValue(function ($value, $entity) {
                return $entity->getHouse() ? '#' . $entity->getHouse()->getId() : '—';
            });
        yield TextareaField::new('comment', 'Комментарий')
            ->hideOnIndex()
            ->setMaxLength(1000);
        yield ChoiceField::new('status', 'Статус')
            ->setChoices([
                'active' => 'Активно',
                'cancelled' => 'Отменено',
                'completed' => 'Завершено',
            ]);
        yield DateTimeField::new('createdAt', 'Дата создания')
            ->setFormat('Y-MM-dd HH:mm')
            ->hideOnForm();
    }
}
