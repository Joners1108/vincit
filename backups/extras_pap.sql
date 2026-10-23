-- ================================================
-- Vincit - EXTRAS PAP
-- Corre DEPOIS do vincit_db.sql (via iniciar_pap.bat)
-- Idempotente: apaga os proprios inserts antes de repor
-- ================================================

SET NAMES utf8mb4;

-- ---- Localizacao da loja: Lisboa -> Porto ----
INSERT INTO `settings` (`setting_key`, `setting_value`)
VALUES ('site_address', 'Porto, Portugal')
ON DUPLICATE KEY UPDATE `setting_value` = 'Porto, Portugal';

-- ---- Admin de demonstracao (admin@vincit.pt / admin) ----
INSERT INTO `users` (`name`,`email`,`password`,`role`,`status`)
VALUES ('Admin','admin@vincit.pt','$2y$10$mzE1n9V6EPwF.1m4h8eveeoU.ndXjKy8Vh2FMpEJ4dUEbKF9F62ku','admin','active')
ON DUPLICATE KEY UPDATE
  `password`='$2y$10$mzE1n9V6EPwF.1m4h8eveeoU.ndXjKy8Vh2FMpEJ4dUEbKF9F62ku',
  `role`='admin', `status`='active';

-- ---- Produtos novos (slugs fixos p/ reimport limpo) ----
DELETE FROM `products` WHERE `slug` IN (
  'camisola-pap-porto','calcas-cargo-pap','tenis-runner-pap','hoodie-pap-oversize'
);

INSERT INTO `products`
(`name`,`slug`,`description`,`short_description`,`price`,`sale_price`,`image`,`gallery`,`stock`,`category_id`,`brand`,`size`,`color`,`material`,`gender`,`featured`,`status`)
VALUES
('Camisola Vincit "Porto"','camisola-pap-porto','Camisola de algodao macio, corte regular. Edicao demonstracao PAP.','Algodao 100%, confortavel para o dia a dia.','29.90',NULL,'demo-vnc-ts-vers.jpg','demo-vnc-ts-vers.jpg',60,3,'Vincit','S, M, L, XL','Preto','Algodao','unissex',1,'active'),
('Calcas Cargo Vincit','calcas-cargo-pap','Calcas cargo com bolsos laterais. Estetica urbana. Demo PAP.','Tecido resistente, corte largo.','44.90','39.90','demo-vnc-cargo-qbr.jpg','demo-vnc-cargo-qbr.jpg',40,4,'Vincit','S, M, L, XL','Verde','Algodao e elastano','homem',1,'active'),
('Tenis Runner Vincit','tenis-runner-pap','Tenis leves para uso diario. Sola em borracha. Demo PAP.','Leves e confortaveis, bom para andar.','59.90',NULL,'demo-vnc-tn-frs.jpg','demo-vnc-tn-frs.jpg',35,5,'Vincit','38, 39, 40, 41, 42, 43','Branco','Malha e borracha','unissex',0,'active'),
('Hoodie Vincit Oversize','hoodie-pap-oversize','Camisola com capuz, corte oversize e interior em felpa. Demo PAP.','Quente e folgada, capuz forrado.','49.90',NULL,'demo-vnc-hood-rua.jpg','demo-vnc-hood-rua.jpg',50,3,'Vincit','S, M, L, XL','Cinza','Algodao e poliester','unissex',1,'active');

-- ---- Tamanhos por produto (com stock) ----
DELETE ps FROM `product_sizes` ps
JOIN `products` p ON p.id = ps.product_id
WHERE p.slug IN ('camisola-pap-porto','calcas-cargo-pap','tenis-runner-pap','hoodie-pap-oversize');

INSERT INTO `product_sizes` (`product_id`,`size_label`,`stock`,`sort_order`)
SELECT p.id, s.lbl, s.stk, s.ord FROM `products` p
JOIN (
  SELECT 'camisola-pap-porto' slug,'S' lbl,15 stk,1 ord UNION ALL
  SELECT 'camisola-pap-porto','M',20,2 UNION ALL
  SELECT 'camisola-pap-porto','L',15,3 UNION ALL
  SELECT 'camisola-pap-porto','XL',10,4 UNION ALL
  SELECT 'calcas-cargo-pap','S',8,1 UNION ALL
  SELECT 'calcas-cargo-pap','M',12,2 UNION ALL
  SELECT 'calcas-cargo-pap','L',12,3 UNION ALL
  SELECT 'calcas-cargo-pap','XL',8,4 UNION ALL
  SELECT 'tenis-runner-pap','38',5,1 UNION ALL
  SELECT 'tenis-runner-pap','39',6,2 UNION ALL
  SELECT 'tenis-runner-pap','40',7,3 UNION ALL
  SELECT 'tenis-runner-pap','41',7,4 UNION ALL
  SELECT 'tenis-runner-pap','42',6,5 UNION ALL
  SELECT 'tenis-runner-pap','43',4,6 UNION ALL
  SELECT 'hoodie-pap-oversize','S',12,1 UNION ALL
  SELECT 'hoodie-pap-oversize','M',15,2 UNION ALL
  SELECT 'hoodie-pap-oversize','L',13,3 UNION ALL
  SELECT 'hoodie-pap-oversize','XL',10,4
) s ON s.slug = p.slug;
