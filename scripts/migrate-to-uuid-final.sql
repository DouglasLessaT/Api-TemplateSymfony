-- Script para migrar IDs de VARCHAR(36) para BINARY(16) UUID
-- Execute com: mysql -u oracle_tgc -poracle_tgc oracle_tgc < scripts/migrate-to-uuid-final.sql

SET FOREIGN_KEY_CHECKS = 0;

-- Remover TODAS as foreign keys existentes
ALTER TABLE inventory_items DROP FOREIGN KEY IF EXISTS FK_3D82424D9EEA759;
ALTER TABLE collections DROP FOREIGN KEY IF EXISTS FK_D325D3EE9EEA759;
ALTER TABLE collection_cards DROP FOREIGN KEY IF EXISTS FK_433AE0AE4ACC9A20;
ALTER TABLE collection_cards DROP FOREIGN KEY IF EXISTS FK_433AE0AE514956FD;
ALTER TABLE decks DROP FOREIGN KEY IF EXISTS FK_A3FCC6329EEA759;
ALTER TABLE deck_cards DROP FOREIGN KEY IF EXISTS FK_C59FA212111948DC;
ALTER TABLE deck_cards DROP FOREIGN KEY IF EXISTS FK_C59FA2124ACC9A20;

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

SET FOREIGN_KEY_CHECKS = 1;

SELECT 'Migração para UUID concluída com sucesso!' AS Status;


