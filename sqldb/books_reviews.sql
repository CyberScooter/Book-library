
CREATE DATABASE books_reviews; -- creates a new database called books_reviews
USE books_reviews; -- uses the database in order to add tables

/*
  Stores the different users that will be using the website, table is needed for registration purposes and being able to link
  reviews with users 
*/
CREATE TABLE users ( 
  Email varchar(35) PRIMARY KEY, -- the email of the user
  Username varchar(10) NOT NULL, -- the username of the user
  Hash varchar(255) NOT NULL -- the hash of the user derived from password
);

/*
  Table bridges the gap between users and reviews, it makes it possible for many users to have many reviews
*/
CREATE TABLE users_reviews (
  ID int PRIMARY KEY AUTO_INCREMENT, 
  ReviewID int NOT NULL, 
  Email varchar(35) NOT NULL, 
  PageID int NOT NULL,
  created_at datetime NOT NULL DEFAULT (now()) 
);

/*
  Table stores the review of a book as well as the reference of it through ISBN
*/
CREATE TABLE reviews (
  ID int PRIMARY KEY AUTO_INCREMENT, 
  ISBN varchar(20) NOT NULL, 
  Review varchar(255), 
  Rating int DEFAULT(0), -- by default rating is 0
  Visible boolean, -- Visible attribute allows review to be public or private to other users
  CHECK (Rating BETWEEN 0 and 10) -- Checks whether the rating is between 0 and 10
);

CREATE TABLE profile (
  Username varchar(10) PRIMARY KEY, -- Unique primary key for the user name of a user
  Bio varchar(40), -- Stores brief description about the user
  Picture varchar(255), -- stores profile picture of the user
  created_at datetime DEFAULT (now()) -- created_at attribute is supposed to set to the current time when data is inserted to other attributes by default
);

CREATE TABLE books (
  ISBN varchar(20) PRIMARY KEY, 
  Author varchar(30) NOT NULL, 
  Title varchar(80) NOT NULL, 
  DateReleased date NOT NULL, 
  Description varchar(255) NOT NULL, 
  Picture varchar(255) -- A front cover of the book
);

CREATE TABLE author (
  Name varchar(30) PRIMARY KEY, -- Name of the author
  DOB date NOT NULL -- Date of birth of the author
);

CREATE TABLE pages (
  ID int PRIMARY KEY AUTO_INCREMENT, -- Unique primary key for the total pages and pages read
  Page int NOT NULL, -- Current page the user is on
  TotalPages int NOT NULL, -- Total pages of the book
  CHECK(Page >= 0 and TotalPages >= 0),
  CHECK(TotalPages >= Page)
);

CREATE TABLE comments (
  ID int PRIMARY KEY AUTO_INCREMENT, -- Unique primary key for id of comment
  Comment varchar(255) -- comment for a review
);

/*
  Table 'posts' links the 'users', 'comments' and 'reviews' tables to be able to process a valid comment for a review
*/
CREATE TABLE posts (
  ID int PRIMARY KEY AUTO_INCREMENT,  -- Unique primary for comment posts
  User varchar(35), 
  CommentID int NOT NULL, 
  ReviewID int NOT NULL, 
  created_at datetime DEFAULT (now()) 
);

CREATE TABLE premium (
  Email varchar(35) PRIMARY KEY NOT NULL, -- Email set as primary key of this table
  BadgeURL varchar(255), -- BadgeURL stores the URL of badge user of user
  BackgroundURL varchar(255) -- BackgroundURL attribute stores the background url for the website
);

CREATE TABLE standard (
  Email varchar(35) PRIMARY KEY NOT NULL, -- Email to be set as primary key of this table
  BooksLimit int DEFAULT(5), -- BooksLimit attribute is the amount of book reviews that can be made, by default the value should be set to 5 
  PrivateReviews int DEFAULT(2), -- PrivateReviews attribute is the amount of private book reviews that can be posted should be default to 2
  CHECK (BooksLimit BETWEEN 0 and 5), -- BooksLimit must be between 0 and 5
  CHECK (PrivateReviews BETWEEN 0 and 2) -- PrivateReviews must be between 0 and 2
);

/*
  Composite key (Email, ReviewID) created as favourites both referencing the foreign keys of the 'users' and 'reviews' table
*/
CREATE TABLE favourites (
  Email varchar(35) NOT NULL, 
  ReviewID int NOT NULL,
  PRIMARY KEY(Email, ReviewID),
  FOREIGN KEY (Email) REFERENCES users(Email),
  FOREIGN KEY (ReviewID) REFERENCES reviews(ID)
);


/*
  Use ALTER TABLE to add foreign keys to tables making it easier to follow through constraints
*/

ALTER TABLE users_reviews ADD FOREIGN KEY (Email) REFERENCES users (Email); -- Email in 'users_reviews' table is a foreign key referencing Email in 'users' table

ALTER TABLE users_reviews ADD FOREIGN KEY (ReviewID) REFERENCES reviews (ID); -- ReviewID in 'users_reviews' table is a foreign key referencing ID in 'reviews' table

ALTER TABLE users ADD FOREIGN KEY (Username) REFERENCES profile (Username); -- Username in 'users' table is a foreign key referencing Username in 'profile' table

ALTER TABLE reviews ADD FOREIGN KEY (ISBN) REFERENCES books (ISBN); -- ISBN in 'reviews' table is a foreign key referencing ISBN in 'books' table

ALTER TABLE books ADD FOREIGN KEY (Author) REFERENCES author (Name); -- Author in 'books' table is a foreign key referencing Name in 'author' table

ALTER TABLE users_reviews ADD FOREIGN KEY (PageID) REFERENCES pages (ID); -- PageID in 'users_reviews' table is a foreign key referencing ID in 'pages' table

ALTER TABLE posts ADD FOREIGN KEY (CommentID) REFERENCES comments (ID); -- CommentID in 'posts' table is a foreign key referencing ID in 'comments' table

ALTER TABLE posts ADD FOREIGN KEY (ReviewID) REFERENCES reviews (ID); -- ReviewID in 'posts' table is a foreign key referencing ReviewID in 'reviews' table

ALTER TABLE posts ADD FOREIGN KEY (User) REFERENCES users (Email); -- User in 'posts' table is a foreign key referencing Email in 'users' table

ALTER Table premium ADD FOREIGN KEY (Email) REFERENCES users (Email); -- Email in 'premium' table is a foreign key referencing Email in 'users' table

ALTER Table standard ADD FOREIGN KEY (Email) REFERENCES users (Email); -- Email in 'standard' table is a foreign key referencing Email in 'users' table

/*
  INSERTING DUMMY DATA INTO TABLES
  Taking care of referential integrity
*/
INSERT INTO profile(Username,Bio,Picture) VALUES ('James','A chill user','profile1.png');

/*
  BCRYPT hash value is used that evaluates to 'test12345'
*/
INSERT INTO users(Email,Username,Hash) VALUES ('test@example.com', 'James', '$2y$10$8/5475dQSBAFEyOxRSonAu145ndf5vJGPXUArdglljPMr0.Iz7Jfq'); -- hash corresponds to password: 'test12345'

INSERT INTO standard(Email,BooksLimit,PrivateReviews) VALUES('test@example.com',4,1);

INSERT INTO author(Name, DOB) VALUES('Jon', '1990-05-05');

INSERT INTO books(ISBN,Author,Title,DateReleased,Description,Picture) VALUES('978-7-8322-9158-5','Jon','A new later','2000-04-04','A book about a magnificient leopard','cover1.png');

INSERT INTO pages(ID, TotalPages, Page) VALUES('1','200','200');

/*
  By default the review and rating of the reviews table should be null and 0
  but as the 'TotalPages' and 'Page' are equal then it would not be a problem in the php
  code to set the 'Review' and 'Rating' below
*/
INSERT INTO reviews(ID,ISBN,Review,Rating,Visible) VALUES('1','978-7-8322-9158-5','A nice book','7','false');

INSERT INTO users_reviews(ID,ReviewID, Email, PageID) VALUES('1','1','test@example.com','1');

/*
  Comment is being added to comments table
  and then added into posts to create the link between the review and comment
*/
INSERT INTO comments(ID,Comment) VALUES('1','Would recommend!');

INSERT INTO posts(User, CommentID, ReviewID) VALUES('test@example.com','1','1');