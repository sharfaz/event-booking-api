<?php

namespace App\Controller\Admin;

use App\Entity\EventBooking;
use App\Enum\EventBookingStatus;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class EventBookingCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return EventBooking::class;
    }

    public function configureFields(string $pageName): iterable
    {
        $statusField = ChoiceField::new('status')
            ->setChoices(EventBookingStatus::cases())
            ->renderAsBadges([
                EventBookingStatus::STATUS_PENDING->value => 'warning',
                EventBookingStatus::STATUS_CANCELLED->value => 'danger',
                EventBookingStatus::STATUS_CONFIRMED->value => 'success',
            ]);



        return [
            IdField::new('id')->hideOnForm(),
            AssociationField::new('attendee'),
            AssociationField::new('event'),
            $statusField,
            DateTimeField::new('bookingDate'),
            DateTimeField::new('createdAt')->hideOnForm(),
            DateTimeField::new('updatedAt')->hideOnForm(),
        ];
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL);
    }

    public function configureCrud(Crud $crud): Crud
    {
        return parent::configureCrud($crud)
            ->setEntityLabelInSingular('Booking')
            ->setEntityLabelInPlural('Bookings');
    }
}
