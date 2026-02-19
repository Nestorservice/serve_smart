USE sigr_restaurant;

-- Ajouter des produits supplémentaires pour tester
-- Récupérer les IDs des catégories
SET @cat_entrees = (SELECT id FROM categories WHERE name_fr = 'Entrées' LIMIT 1);
SET @cat_plats = (SELECT id FROM categories WHERE name_fr = 'Plats Principaux' LIMIT 1);
SET @cat_grillades = (SELECT id FROM categories WHERE name_fr = 'Grillades' LIMIT 1);
SET @cat_accomp = (SELECT id FROM categories WHERE name_fr = 'Accompagnements' LIMIT 1);
SET @cat_boissons = (SELECT id FROM categories WHERE name_fr = 'Boissons' LIMIT 1);
SET @cat_desserts = (SELECT id FROM categories WHERE name_fr = 'Desserts' LIMIT 1);

-- Entrées supplémentaires
INSERT IGNORE INTO products (category_id, name_fr, name_en, description_fr, description_en, price, preparation_time, is_available, is_featured, requires_stock) VALUES
(@cat_entrees, 'Beignets de Crevettes', 'Shrimp Fritters', 'Crevettes panées croustillantes servies avec sauce piquante', 'Crispy breaded shrimp with spicy sauce', 4500, 12, TRUE, TRUE, FALSE),
(@cat_entrees, 'Salade César', 'Caesar Salad', 'Salade romaine, croûtons, parmesan et sauce César', 'Romaine, croutons, parmesan and Caesar dressing', 3500, 8, TRUE, FALSE, FALSE),
(@cat_entrees, 'Accras de Morue', 'Cod Fritters', 'Beignets de morue épicés traditionnels', 'Traditional spiced cod fritters', 3000, 10, TRUE, FALSE, FALSE);

-- Plats supplémentaires
INSERT IGNORE INTO products (category_id, name_fr, name_en, description_fr, description_en, price, preparation_time, is_available, is_featured, requires_stock) VALUES
(@cat_plats, 'Ndolé', 'Ndolé', 'Plat camerounais aux feuilles amères et crevettes', 'Cameroonian bitter leaf and shrimp dish', 6000, 35, TRUE, TRUE, FALSE),
(@cat_plats, 'Eru', 'Eru', 'Légumes verts sautés à l''huile de palme avec viande', 'Green vegetables in palm oil with meat', 5500, 30, TRUE, FALSE, FALSE),
(@cat_plats, 'Thieboudienne', 'Thieboudienne', 'Riz au poisson à la sénégalaise', 'Senegalese fish and rice', 5000, 35, TRUE, FALSE, FALSE),
(@cat_plats, 'Yassa Poulet', 'Chicken Yassa', 'Poulet mariné aux oignons et citron', 'Chicken marinated with onions and lemon', 5500, 30, TRUE, TRUE, FALSE),
(@cat_plats, 'Mafé', 'Mafé', 'Ragoût de boeuf à la pâte d''arachide', 'Beef stew with peanut paste', 5000, 30, TRUE, FALSE, FALSE);

-- Grillades supplémentaires
INSERT IGNORE INTO products (category_id, name_fr, name_en, description_fr, description_en, price, preparation_time, is_available, is_featured, requires_stock) VALUES
(@cat_grillades, 'Soya', 'Soya', 'Brochettes de boeuf épicées camerounaises', 'Cameroonian spiced beef skewers', 3500, 10, TRUE, TRUE, FALSE),
(@cat_grillades, 'Poisson Grillé Entier', 'Whole Grilled Fish', 'Poisson entier grillé au charbon', 'Whole charcoal-grilled fish', 7000, 25, TRUE, FALSE, FALSE),
(@cat_grillades, 'Poulet en Papillote', 'Chicken Papillote', 'Poulet grillé en feuille de bananier', 'Chicken grilled in banana leaf', 5500, 20, TRUE, FALSE, FALSE);

-- Accompagnements supplémentaires
INSERT IGNORE INTO products (category_id, name_fr, name_en, description_fr, description_en, price, preparation_time, is_available, is_featured, requires_stock) VALUES
(@cat_accomp, 'Couscous de Maïs', 'Corn Couscous', 'Couscous de maïs fait maison', 'Homemade corn couscous', 1200, 5, TRUE, FALSE, FALSE),
(@cat_accomp, 'Frites Maison', 'Homemade Fries', 'Pommes de terre coupées main et frites', 'Hand-cut and fried potatoes', 1500, 12, TRUE, FALSE, FALSE),
(@cat_accomp, 'Miondo', 'Miondo', 'Bâtons de manioc cuits à la vapeur', 'Steamed cassava sticks', 500, 5, TRUE, FALSE, FALSE);

-- Boissons supplémentaires
INSERT IGNORE INTO products (category_id, name_fr, name_en, description_fr, description_en, price, preparation_time, is_available, is_featured, requires_stock) VALUES
(@cat_boissons, 'Sprite', 'Sprite', 'Canette 33cl bien fraîche', 'Cold 33cl can', 1000, 1, TRUE, FALSE, TRUE),
(@cat_boissons, 'Jus de Baobab', 'Baobab Juice', 'Jus de bouye naturel 25cl', 'Natural bouye juice 25cl', 1500, 5, TRUE, FALSE, FALSE),
(@cat_boissons, 'Bière Castel', 'Castel Beer', 'Bouteille 65cl bien fraîche', 'Cold 65cl bottle', 1500, 1, TRUE, FALSE, TRUE),
(@cat_boissons, 'Jus d''Ananas', 'Pineapple Juice', 'Jus d''ananas frais pressé 25cl', 'Fresh pressed pineapple juice 25cl', 1500, 5, TRUE, TRUE, FALSE),
(@cat_boissons, 'Bissap', 'Hibiscus Juice', 'Jus d''hibiscus rafraîchissant 25cl', 'Refreshing hibiscus juice 25cl', 1200, 5, TRUE, FALSE, FALSE);

-- Desserts supplémentaires
INSERT IGNORE INTO products (category_id, name_fr, name_en, description_fr, description_en, price, preparation_time, is_available, is_featured, requires_stock) VALUES
(@cat_desserts, 'Crêpes au Miel', 'Honey Crepes', 'Crêpes chaudes nappées de miel local', 'Warm crepes with local honey', 2000, 8, TRUE, FALSE, FALSE),
(@cat_desserts, 'Beignets Sucrés', 'Sweet Doughnuts', 'Beignets moelleux saupoudrés de sucre', 'Soft doughnuts with sugar', 1500, 10, TRUE, FALSE, FALSE),
(@cat_desserts, 'Glace Artisanale', 'Artisan Ice Cream', '2 boules au choix: vanille, chocolat, mangue', '2 scoops: vanilla, chocolate, mango', 2000, 3, TRUE, TRUE, TRUE);

-- Ajouter le stock pour les nouveaux produits qui en nécessitent
INSERT INTO stock (product_id, quantity, unit, low_threshold)
SELECT id, 
    CASE 
        WHEN name_fr LIKE '%Bière%' OR name_fr LIKE '%Castel%' THEN 48
        WHEN name_fr LIKE '%Eau%' THEN 100
        WHEN name_fr LIKE '%Gâteau%' OR name_fr LIKE '%Glace%' THEN 15
        ELSE 50
    END,
    'unité', 10
FROM products 
WHERE requires_stock = TRUE 
AND id NOT IN (SELECT product_id FROM stock);

-- Afficher le résultat
SELECT 
    (SELECT COUNT(*) FROM categories) AS total_categories,
    (SELECT COUNT(*) FROM products) AS total_products,
    (SELECT COUNT(*) FROM stock) AS total_stock,
    (SELECT COUNT(*) FROM restaurant_tables) AS total_tables;
