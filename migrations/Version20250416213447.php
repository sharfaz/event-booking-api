<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250416213447 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create initial database schema for event booking system';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE admin_users (
              id SERIAL NOT NULL,
              first_name VARCHAR(100) NOT NULL,
              last_name VARCHAR(100) NOT NULL,
              email VARCHAR(180) NOT NULL,
              password VARCHAR(255) NOT NULL,
              roles JSON NOT NULL,
              created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL DEFAULT now(),
              updated_at TIMESTAMP(0) WITH TIME ZONE NOT NULL DEFAULT now(),
              PRIMARY KEY(id)
            )
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL ON admin_users (email)
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN admin_users.created_at IS '(DC2Type:datetime_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN admin_users.updated_at IS '(DC2Type:datetime_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE attendees (
              id SERIAL NOT NULL,
              first_name VARCHAR(100) NOT NULL,
              last_name VARCHAR(100) NOT NULL,
              email VARCHAR(255) NOT NULL,
              date_of_birth DATE DEFAULT NULL,
              created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL DEFAULT now(),
              updated_at TIMESTAMP(0) WITH TIME ZONE NOT NULL DEFAULT now(),
              PRIMARY KEY(id)
            )
        SQL);

        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN attendees.created_at IS '(DC2Type:datetime_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN attendees.updated_at IS '(DC2Type:datetime_immutable)'
        SQL);

        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_IDENTIFIER_ATTENDEE_EMAIL ON attendees (email)
        SQL);

        $this->addSql(<<<'SQL'
            CREATE TABLE event_bookings (
              id SERIAL NOT NULL,
              event_id INT DEFAULT NULL,
              attendee_id INT DEFAULT NULL,
              booking_date TIMESTAMP(0) WITH TIME ZONE NOT NULL,
              created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL DEFAULT now(),
              updated_at TIMESTAMP(0) WITH TIME ZONE NOT NULL DEFAULT now(),
              PRIMARY KEY(id)
            )
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_3C69D5E53E5F2F7B ON event_bookings (event_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_3C69D5E577717BE5 ON event_bookings (attendee_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_EVENT_BOOKINGS ON event_bookings (event_id, attendee_id)
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN event_bookings.created_at IS '(DC2Type:datetime_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN event_bookings.updated_at IS '(DC2Type:datetime_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE events (
              id SERIAL NOT NULL,
              name VARCHAR(255) NOT NULL,
              description TEXT NOT NULL,
              event_date TIMESTAMP(0) WITH TIME ZONE NOT NULL,
              capacity INT NOT NULL,
              country VARCHAR(100) NOT NULL,
              created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL DEFAULT now(),
              updated_at TIMESTAMP(0) WITH TIME ZONE NOT NULL DEFAULT now(),
              PRIMARY KEY(id)
            )
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN events.created_at IS '(DC2Type:datetime_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN events.updated_at IS '(DC2Type:datetime_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              event_bookings
            ADD
              CONSTRAINT FK_3C69D5E53E5F2F7B FOREIGN KEY (event_id) REFERENCES events (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              event_bookings
            ADD
              CONSTRAINT FK_3C69D5E577717BE5 FOREIGN KEY (attendee_id) REFERENCES attendees (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE event_bookings DROP CONSTRAINT FK_3C69D5E53E5F2F7B
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE event_bookings DROP CONSTRAINT FK_3C69D5E577717BE5
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE admin_users
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE attendees
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE event_bookings
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE events
        SQL);
    }
}
