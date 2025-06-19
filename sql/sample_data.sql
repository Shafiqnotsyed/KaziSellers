-- KaziSellers Consolidated Sample Data
-- Updated sample data for the consolidated database structure

-- Use the KaziSellers database
USE Kazi_sellers;

-- ========================================
-- CLEAR EXISTING DATA FIRST
-- ========================================

-- Disable foreign key checks temporarily
SET FOREIGN_KEY_CHECKS = 0;

-- Clear existing data in proper order
DELETE FROM cart;
DELETE FROM product_ratings;
DELETE FROM favorites;
DELETE FROM ratings;
DELETE FROM messages;
DELETE FROM product_images;
DELETE FROM products;
DELETE FROM users WHERE role = 0; -- Keep admin user
DELETE FROM categories;

-- Reset auto-increment counters
ALTER TABLE cart AUTO_INCREMENT = 1;
ALTER TABLE product_ratings AUTO_INCREMENT = 1;
ALTER TABLE favorites AUTO_INCREMENT = 1;
ALTER TABLE ratings AUTO_INCREMENT = 1;
ALTER TABLE messages AUTO_INCREMENT = 1;
ALTER TABLE product_images AUTO_INCREMENT = 1;
ALTER TABLE products AUTO_INCREMENT = 1;
ALTER TABLE users AUTO_INCREMENT = 2; -- Start from 2 since admin is ID 1
ALTER TABLE categories AUTO_INCREMENT = 1;

-- Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;

-- Re-insert categories
INSERT INTO categories (name, description, icon) VALUES
('Electronics', 'Phones, laptops, gadgets, and tech accessories', 'fa-laptop'),
('Clothing', 'Fashion, shoes, accessories, and apparel', 'fa-tshirt'),
('Books', 'Books, magazines, and reading materials', 'fa-book'),
('Furniture', 'Home and office furniture, decor items', 'fa-couch'),
('Sports', 'Sporting goods, fitness equipment, and gear', 'fa-futbol'),
('Other', 'Everything else not covered in other categories', 'fa-box');

-- ========================================
-- SAMPLE USERS
-- ========================================

-- Sample Users (password for all: 'password123' - properly hashed)
INSERT INTO users (username, email, password, first_name, last_name, phone, location, bio, rating, total_ratings, role) VALUES
('mike_johnson', 'mike.johnson@email.com', '$2y$10$ipcQOCQd.B0ktiQd6mGtJOTdME/IsOFn1j/mDJPPM/obOI327ifWu', 'Mike', 'Johnson', '0612345678', 'Johannesburg', 'Tech enthusiast and gadget collector. Selling quality electronics.', 4.5, 12, 0),
('sarah_williams', 'sarah.williams@email.com', '$2y$10$ipcQOCQd.B0ktiQd6mGtJOTdME/IsOFn1j/mDJPPM/obOI327ifWu', 'Sarah', 'Williams', '0723456789', 'Cape Town', 'Fashion lover and trendsetter. Love finding and selling unique pieces!', 4.8, 25, 0),
('david_smith', 'david.smith@email.com', '$2y$10$ipcQOCQd.B0ktiQd6mGtJOTdME/IsOFn1j/mDJPPM/obOI327ifWu', 'David', 'Smith', '0834567890', 'Durban', 'Book collector and reading enthusiast. Great deals on books and electronics.', 4.2, 8, 0),
('lisa_brown', 'lisa.brown@email.com', '$2y$10$ipcQOCQd.B0ktiQd6mGtJOTdME/IsOFn1j/mDJPPM/obOI327ifWu', 'Lisa', 'Brown', '0745678901', 'Pretoria', 'Home decorator and furniture enthusiast. Quality furniture at great prices.', 4.7, 15, 0),
('james_wilson', 'james.wilson@email.com', '$2y$10$ipcQOCQd.B0ktiQd6mGtJOTdME/IsOFn1j/mDJPPM/obOI327ifWu', 'James', 'Wilson', '0656789012', 'Port Elizabeth', 'Sports and fitness enthusiast. Buying and selling quality equipment.', 4.3, 6, 0),
('emma_taylor', 'emma.taylor@email.com', '$2y$10$ipcQOCQd.B0ktiQd6mGtJOTdME/IsOFn1j/mDJPPM/obOI327ifWu', 'Emma', 'Taylor', '0787654321', 'Bloemfontein', 'Student looking for textbooks and affordable electronics.', 4.1, 3, 0);

-- ========================================
-- SAMPLE PRODUCTS
-- ========================================

-- Sample Products
INSERT INTO products (seller_id, category_id, title, description, short_description, price, condition_type, location, views) VALUES
-- Electronics (Category 1) - sold by Mike (ID 2) and David (ID 4)
(2, 1, 'iPhone 12 Pro Max 128GB', 'Excellent condition iPhone 12 Pro Max in Space Gray. No scratches on screen, comes with original box and charger. Battery health at 89%. Perfect phone for anyone who needs reliability and performance. Recently upgraded to iPhone 14, so selling this one.', 'iPhone 12 Pro Max, excellent condition, original box', 12500.00, 'like_new', 'Sandton, Johannesburg', 145),
(4, 1, 'MacBook Air M1 2020', 'Barely used MacBook Air with M1 chip. Comes with original packaging, charger, and protective case. Perfect for work, creativity, and entertainment. No dents or scratches. Used mainly for university work.', 'MacBook Air M1, like new, includes case', 18900.00, 'like_new', 'Westville, Durban', 278),
(2, 1, 'Samsung Galaxy Earbuds Pro', 'Premium wireless earbuds with active noise cancellation. Perfect for music, calls, and everyday use. Comes with charging case and all original accessories. Used for 6 months, excellent sound quality.', 'Samsung Galaxy Earbuds Pro with ANC', 2100.00, 'good', 'Rosebank, Johannesburg', 93),
(6, 1, 'HP Gaming Laptop', 'Powerful gaming laptop perfect for gaming and professional work. Intel i7, 16GB RAM, GTX 1660Ti. Great for gaming, video editing, and demanding applications. Minor wear on palm rest.', 'Gaming laptop, i7, 16GB RAM, GTX 1660Ti', 15800.00, 'good', 'Summerstrand, Port Elizabeth', 167),

-- Clothing (Category 2) - sold by Sarah (ID 3) and Lisa (ID 5)
(3, 2, 'Vintage Denim Jacket', 'Trendy oversized denim jacket, perfect for any season. Size Medium but fits like Large. Vintage wash with some natural distressing. Great for layering and style. From a smoke-free home.', 'Vintage denim jacket, oversized fit', 450.00, 'good', 'Observatory, Cape Town', 84),
(3, 2, 'Designer Sneakers - Nike Air Max', 'Limited edition Nike Air Max 270 in great condition. Size 8 (mens)/9.5 (womens). Only worn a few times, mostly for special occasions. Perfect for casual or athletic wear. No box included.', 'Nike Air Max 270, limited edition, size 8', 1850.00, 'like_new', 'Claremont, Cape Town', 156),
(5, 2, 'Professional Blazer', 'Navy blue blazer perfect for business meetings and formal occasions. Size 10. Dry cleaned and well maintained. Essential for any professional wardrobe. From premium brand.', 'Navy blazer, size 10, professional', 680.00, 'good', 'Brooklyn, Pretoria', 49),
(3, 2, 'Summer Dress Collection', 'Beautiful collection of 3 summer dresses, sizes 8-10. Perfect for warm weather and special occasions. All in excellent condition, barely worn. Various colors and styles included.', 'Summer dress collection, 3 dresses, sizes 8-10', 850.00, 'like_new', 'Camps Bay, Cape Town', 72),

-- Books (Category 3) - sold by David (ID 4) and Emma (ID 7)
(5, 3, 'Business Book Collection', 'Collection of 5 popular business and self-help books: Rich Dad Poor Dad, Think and Grow Rich, Good to Great, The Lean Startup, and Atomic Habits. All current editions. Minimal wear, great condition.', 'Business book collection, 5 books, excellent condition', 850.00, 'good', 'Centurion, Pretoria', 189),
(4, 3, 'Fiction Novel Bundle', 'Set of 8 popular fiction novels including bestsellers and award winners. Includes works by John Grisham, Stephen King, and Dan Brown. All books in excellent condition with minimal wear.', 'Fiction novel bundle, 8 bestsellers included', 420.00, 'good', 'Morningside, Durban', 142),
(2, 3, 'Cooking and Recipe Books', 'Collection of 6 cooking and recipe books covering various cuisines and cooking techniques. All latest editions. Perfect for cooking enthusiasts. Light use, no missing pages or stains.', 'Cooking book collection, 6 books, various cuisines', 380.00, 'good', 'Melville, Johannesburg', 173),
(7, 3, 'University Textbooks - Engineering', 'Set of engineering textbooks for 2nd and 3rd year students. Includes Mathematics, Physics, and Engineering fundamentals. All in good condition with some highlighting. Great for students.', 'Engineering textbooks, 2nd-3rd year, good condition', 1200.00, 'good', 'Hatfield, Pretoria', 98),

-- Furniture (Category 4) - sold by Lisa (ID 5) and others
(5, 4, 'Modern Study Desk', 'White wooden desk perfect for home office or study room. Two drawers for storage, cable management holes. Slight marks on surface but very functional. Easy to assemble, screws included.', 'White modern desk with 2 drawers', 750.00, 'good', 'Hatfield, Pretoria', 128),
(3, 4, 'Mini Fridge - Compact', 'Compact fridge perfect for office, bedroom, or small apartment. 120L capacity, excellent working condition. Great for storing drinks and snacks. Energy efficient, very quiet operation.', 'Mini fridge, 120L, excellent condition', 1900.00, 'good', 'Vredehoek, Cape Town', 251),
(4, 4, 'Ergonomic Office Chair', 'Comfortable office chair with lumbar support. Perfect for long work sessions. Height adjustable, slight wear on armrests but very comfortable. Black mesh design, breathable material.', 'Ergonomic office chair, height adjustable', 950.00, 'good', 'Glenwood, Durban', 137),
(5, 4, 'Dining Table Set', 'Beautiful wooden dining table with 4 chairs. Perfect for small families or apartments. Solid wood construction, well-maintained. Minor scratches on table surface but overall excellent condition.', 'Wooden dining set, table + 4 chairs', 2800.00, 'good', 'Lynnwood, Pretoria', 89),

-- Sports (Category 5) - sold by James (ID 6) and others
(6, 5, 'Soccer Boots - Adidas', 'Adidas Predator soccer boots, size 9. Used for one season. Still have plenty of grip and excellent condition. Perfect for recreational or competitive play. Comes with original box.', 'Adidas Predator boots, size 9, good grip', 680.00, 'good', 'Newton Park, Port Elizabeth', 122),
(2, 5, 'Home Gym Equipment Set', 'Adjustable dumbbells (5-20kg each), yoga mat, resistance bands, and exercise ball. Perfect for home workouts. Everything in excellent condition. Ideal for fitness enthusiasts.', 'Complete home gym set, dumbbells + accessories', 1200.00, 'like_new', 'Randburg, Johannesburg', 144),
(6, 5, 'Tennis Racket - Wilson', 'Professional Wilson tennis racket in excellent condition. Great for intermediate to advanced players. Comes with protective cover and extra grip tape. Recently restrung.', 'Wilson tennis racket, professional grade', 850.00, 'like_new', 'Humewood, Port Elizabeth', 76),

-- Other (Category 6) - various sellers
(3, 6, 'Mountain Bike - Trek', 'Trek mountain bike, perfect for commuting and weekend trails. 21-speed, recently serviced. New tires and brakes. Lock and helmet included for safety. Great for daily use.', 'Trek mountain bike, 21-speed, includes accessories', 3500.00, 'good', 'Newlands, Cape Town', 265),
(5, 6, 'Coffee Machine - Nespresso', 'Nespresso coffee machine, perfect for coffee lovers. Makes barista-quality coffee at home. Includes starter pack of 40 capsules. Excellent working condition, barely used.', 'Nespresso machine, includes 40 capsules', 1350.00, 'like_new', 'Menlyn, Pretoria', 138),
(7, 6, 'Musical Keyboard - Yamaha', 'Yamaha keyboard perfect for beginners and intermediate players. 61 keys, multiple sounds and rhythms. Comes with stand, music book, and power adapter. Great for learning.', 'Yamaha keyboard, 61 keys, includes stand', 2200.00, 'good', 'Arcadia, Pretoria', 91);

-- ========================================
-- SAMPLE PRODUCT IMAGES
-- ========================================

-- Sample Product Images (using existing image or placeholder paths)
INSERT INTO product_images (product_id, image_path, is_primary, upload_order) VALUES
(1, 'uploads/product_684167e1d34b6_1749116897.jpg', TRUE, 0),
(1, 'uploads/iphone12_2.jpg', FALSE, 1),
(2, 'uploads/macbook_1.jpg', TRUE, 0),
(2, 'uploads/macbook_2.jpg', FALSE, 1),
(3, 'uploads/earbuds_1.jpg', TRUE, 0),
(4, 'uploads/laptop_1.jpg', TRUE, 0),
(5, 'uploads/jacket_1.jpg', TRUE, 0),
(6, 'uploads/sneakers_1.jpg', TRUE, 0),
(7, 'uploads/blazer_1.jpg', TRUE, 0),
(8, 'uploads/dresses_1.jpg', TRUE, 0),
(9, 'uploads/business_books_1.jpg', TRUE, 0),
(10, 'uploads/fiction_books_1.jpg', TRUE, 0),
(11, 'uploads/cooking_books_1.jpg', TRUE, 0),
(12, 'uploads/textbooks_1.jpg', TRUE, 0),
(13, 'uploads/desk_1.jpg', TRUE, 0),
(14, 'uploads/fridge_1.jpg', TRUE, 0),
(15, 'uploads/chair_1.jpg', TRUE, 0),
(16, 'uploads/dining_set_1.jpg', TRUE, 0),
(17, 'uploads/boots_1.jpg', TRUE, 0),
(18, 'uploads/gym_set_1.jpg', TRUE, 0),
(19, 'uploads/tennis_racket_1.jpg', TRUE, 0),
(20, 'uploads/bike_1.jpg', TRUE, 0),
(21, 'uploads/coffee_1.jpg', TRUE, 0),
(22, 'uploads/keyboard_1.jpg', TRUE, 0);

-- ========================================
-- SAMPLE MESSAGES
-- ========================================

-- Sample Messages between users
INSERT INTO messages (product_id, sender_id, receiver_id, message, is_read) VALUES
(1, 3, 2, 'Hi! Is the iPhone still available? I am very interested in purchasing it.', TRUE),
(1, 2, 3, 'Yes, it is still available! When would you like to meet for pickup?', TRUE),
(1, 3, 2, 'How about tomorrow afternoon? I am available after 2pm in Sandton area.', FALSE),
(2, 5, 4, 'Is the MacBook still under warranty? And do you have the original receipt?', TRUE),
(2, 4, 5, 'Yes, Apple warranty valid until next year. I have all original documents and receipt.', FALSE),
(9, 2, 5, 'Do you have all the books in the business collection? Can you send me the list?', TRUE),
(9, 5, 2, 'Yes, all 5 books included: Rich Dad Poor Dad, Think and Grow Rich, Good to Great, The Lean Startup, and Atomic Habits.', FALSE),
(20, 6, 3, 'Is the bike suitable for casual riding and daily commuting to work?', TRUE),
(20, 3, 6, 'Absolutely! It is perfect for daily rides and weekend adventures. Very reliable.', FALSE),
(14, 4, 3, 'Does the mini fridge make much noise? I need it for my bedroom.', TRUE),
(14, 3, 4, 'It is very quiet! Perfect for bedrooms. Energy efficient too.', FALSE),
(18, 7, 2, 'Are all the gym equipment pieces included? Any missing parts?', TRUE),
(18, 2, 7, 'Everything is complete! Dumbbells, mat, bands, and exercise ball. All in excellent condition.', FALSE);

-- ========================================
-- SAMPLE USER RATINGS
-- ========================================

-- Sample User Ratings (rating users based on transactions)
INSERT INTO ratings (rater_id, rated_user_id, product_id, rating, review) VALUES
(3, 2, 3, 5, 'Excellent seller! Fast response and item exactly as described. Very professional and trustworthy.'),
(5, 4, 10, 4, 'Good books, slight delay in meeting but overall very satisfied with purchase. Books in great condition.'),
(2, 3, 5, 5, 'Amazing jacket! Perfect fit and exactly what I was looking for. Sarah was very helpful.'),
(4, 5, 9, 5, 'Great book collection, saved me a lot of money. Very happy with the purchase! Lisa is a fantastic seller.'),
(6, 2, 18, 4, 'Good gym equipment set, minor issue with one item but seller was very helpful and responsive.'),
(2, 3, 14, 5, 'Fridge works perfectly! Great quality and exactly as advertised. Highly recommend Sarah.'),
(3, 5, 13, 4, 'Nice desk, had a few more scratches than expected but good value for money. Fast delivery.'),
(7, 2, 11, 5, 'Cooking books are in perfect condition! Mike was very accommodating with the meeting time.'),
(4, 3, 20, 5, 'Excellent bike! Works perfectly and Sarah included extra accessories. Very satisfied.'),
(5, 4, 15, 4, 'Chair is comfortable but had some wear not mentioned in description. Still good value.');

-- ========================================
-- SAMPLE PRODUCT RATINGS
-- ========================================

-- Sample Product Ratings (users rating specific products)
INSERT INTO product_ratings (product_id, user_id, rating, review) VALUES
(1, 3, 5, 'Amazing phone! Battery life is still excellent and performance is top-notch. Worth every penny.'),
(1, 5, 4, 'Great phone, exactly as described. Minor wear but overall very happy with the purchase.'),
(2, 6, 5, 'Perfect laptop for university work. Fast, reliable, and the M1 chip is incredible. Highly recommend!'),
(3, 4, 4, 'Good earbuds with excellent sound quality. Noise cancellation works well. Slight connection issues initially.'),
(9, 2, 5, 'Fantastic book collection! All books are current editions and in excellent condition. Great value.'),
(9, 7, 5, 'Perfect for my business studies. Books are well-maintained and very informative. Excellent deal.'),
(14, 4, 5, 'Perfect mini fridge! Quiet operation and keeps everything cold. Great for my office.'),
(18, 7, 4, 'Good gym set, everything works well. The dumbbells are adjustable and easy to use. Minor wear on mat.'),
(20, 4, 5, 'Excellent bike! Perfect for my daily commute and weekend rides. Very well maintained.'),
(21, 6, 5, 'Best coffee machine! Makes perfect espresso every time. The included capsules were a nice bonus.');

-- ========================================
-- SAMPLE FAVORITES/WISHLIST
-- ========================================

-- Sample Favorites (users saving products for later)
INSERT INTO favorites (user_id, product_id) VALUES
(2, 2), -- Mike likes the MacBook
(2, 20), -- Mike likes the bike
(3, 4), -- Sarah likes the gaming laptop
(3, 15), -- Sarah likes the office chair
(4, 1), -- David likes the iPhone
(4, 11), -- David likes the cooking books
(5, 6), -- Lisa likes the sneakers
(5, 18), -- Lisa likes the gym set
(6, 9), -- James likes the business books
(6, 21), -- James likes the coffee machine
(7, 12), -- Emma likes the textbooks
(7, 13), -- Emma likes the study desk
(2, 19), -- Mike likes the tennis racket
(3, 22), -- Sarah likes the keyboard
(4, 16); -- David likes the dining set

-- ========================================
-- SAMPLE CART ITEMS
-- ========================================

-- Sample Cart items (users have items in their cart)
INSERT INTO cart (user_id, product_id, quantity) VALUES
(3, 1, 1), -- Sarah has iPhone in cart
(3, 11, 1), -- Sarah has cooking books in cart
(4, 6, 1), -- David has sneakers in cart
(5, 2, 1), -- Lisa has MacBook in cart
(5, 18, 1), -- Lisa has gym set in cart
(6, 9, 1), -- James has business books in cart
(6, 17, 1), -- James has soccer boots in cart
(7, 12, 2), -- Emma has 2 textbook sets in cart
(2, 14, 1), -- Mike has mini fridge in cart
(2, 19, 1); -- Mike has tennis racket in cart

-- ========================================
-- DATA SUMMARY
-- ========================================
-- This sample data includes:
-- - 6 regular users + 1 admin user
-- - 22 products across all 6 categories
-- - Product images for all products
-- - 13 message conversations
-- - 10 user ratings
-- - 10 product ratings  
-- - 15 wishlist items
-- - 10 cart items
-- - Realistic South African locations and prices
-- - Proper relationships between all tables
-- - Test data for all major platform features
