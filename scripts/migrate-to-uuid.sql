-- Script para migrar IDs de VARCHAR(36) para BINARY(16) UUID
-- Execute com: mysql -u oracle_tgc -poracle_tgc oracle_tgc < scripts/migrate-to-uuid.sql

-- Desabilitar verificações de foreign key temporariamente
SET FOREIGN_KEY_CHECKS = 0;

-- Remover todas as foreign keys
ALTER TABLE inventory_items DROP FOREIGN KEY IF EXISTS FK_3D82424D4ACC9A20;
ALTER TABLE inventory_items DROP FOREIGN KEY IF EXISTS FK_3D82424D76ED395;
ALTER TABLE collections DROP FOREIGN KEY IF EXISTS FK_5FC0AA6C339A60D;
ALTER TABLE collection_cards DROP FOREIGN KEY IF EXISTS FK_5FC0AA6C339A60D;
ALTER TABLE collection_cards DROP FOREIGN KEY IF EXISTS FK_5FC0AA6C4ACC9A20;
ALTER TABLE decks DROP FOREIGN KEY IF EXISTS FK_3D82424D339A60D;
ALTER TABLE deck_cards DROP FOREIGN KEY IF EXISTS FK_3D82424D111948DC;
ALTER TABLE deck_cards DROP FOREIGN KEY IF EXISTS FK_3D82424D4ACC9A20;

-- Alterar tipos de ID para BINARY(16) (UUID)
ALTER TABLE users MODIFY COLUMN id BINARY(16) NOT NULL;
ALTER TABLE card MODIFY COLUMN id BINARY(16) NOT NULL;
ALTER TABLE inventories MODIFY COLUMN id BINARY(16) NOT NULL;
ALTER TABLE inventories MODIFY COLUMN user_id BINARY(16) NOT NULL;
ALTER TABLE inventory_items MODIFY COLUMN id BINARY(16) NOT NULL;
ALTER TABLE inventory_items MODIFY COLUMN inventory_id BINARY(16) NOT NULL;
ALTER TABLE inventory_items MODIFY COLUMN card_id BINARY(16) NOT NULL;
ALTER TABLE collections MODIFY COLUMN id BINARY(16) NOT NULL;
ALTER TABLE collections MODIFY COLUMN inventory_id BINARY(16) NOT NULL;
ALTER TABLE collection_cards MODIFY COLUMN collection_id BINARY(16) NOT NULL;
ALTER TABLE collection_cards MODIFY COLUMN card_id BINARY(16) NOT NULL;
ALTER TABLE decks MODIFY COLUMN id BINARY(16) NOT NULL;
ALTER TABLE decks MODIFY COLUMN inventory_id BINARY(16) NOT NULL;
ALTER TABLE deck_cards MODIFY COLUMN id BINARY(16) NOT NULL;
ALTER TABLE deck_cards MODIFY COLUMN deck_id BINARY(16) NOT NULL;
ALTER TABLE deck_cards MODIFY COLUMN card_id BINARY(16) NOT NULL;

-- Recriar foreign keys
ALTER TABLE inventory_items 
    ADD CONSTRAINT FK_inventory_items_inventory 
    FOREIGN KEY (inventory_id) REFERENCES inventories(id) ON DELETE CASCADE;

ALTER TABLE inventory_items 
    ADD CONSTRAINT FK_inventory_items_card 
    FOREIGN KEY (card_id) REFERENCES card(id) ON DELETE CASCADE;

ALTER TABLE collections 
    ADD CONSTRAINT FK_collections_inventory 
    FOREIGN KEY (inventory_id) REFERENCES inventories(id) ON DELETE CASCADE;

ALTER TABLE collection_cards 
    ADD CONSTRAINT FK_collection_cards_collection 
    FOREIGN KEY (collection_id) REFERENCES collections(id) ON DELETE CASCADE;

ALTER TABLE collection_cards 
    ADD CONSTRAINT FK_collection_cards_card 
    FOREIGN KEY (card_id) REFERENCES card(id) ON DELETE CASCADE;

ALTER TABLE decks 
    ADD CONSTRAINT FK_decks_inventory 
    FOREIGN KEY (inventory_id) REFERENCES inventories(id) ON DELETE CASCADE;

ALTER TABLE deck_cards 
    ADD CONSTRAINT FK_deck_cards_deck 
    FOREIGN KEY (deck_id) REFERENCES decks(id) ON DELETE CASCADE;

ALTER TABLE deck_cards 
    ADD CONSTRAINT FK_deck_cards_card 
    FOREIGN KEY (card_id) REFERENCES card(id) ON DELETE CASCADE;

-- Reabilitar verificações de foreign key
SET FOREIGN_KEY_CHECKS = 1;

SELECT 'Migração para UUID concluída com sucesso!' AS Status;


