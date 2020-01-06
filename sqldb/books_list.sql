
CREATE DATABASE books_list;
USE books_list;

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
  ID int PRIMARY KEY AUTO_INCREMENT, -- Unique primary key for each record to allow access to multiple user reviews later in PHP
  ReviewID int NOT NULL, -- ReviewID attribute which is set to the foreign key of the ID attribute in reviews table later
  Email varchar(35) NOT NULL, -- Email attribute which is set to the foreign key of the Email attribute in the users table later
  PageID int NOT NULL, -- PageID attribute which is set to the foreign key of the ID attribute in the pages table later
  created_at datetime NOT NULL DEFAULT (now()) -- created_at attribute is supposed to set to the current time when data is inserted to other attributes by default
);

/*
  Table stores the review of a book as well as the reference of it
*/
CREATE TABLE reviews (
  ID int PRIMARY KEY AUTO_INCREMENT, -- Unique primary key for each record needed to allow access to a review later in PHP
  ISBN varchar(13) NOT NULL, -- ISBN attribute stores isbn of the book has to be not null because will be referenced as foreign key to books isbn
  Review varchar(255), -- Review attribute stores the review of the book
  Rating int DEFAULT 0, -- Rating attribute stores the rating of the book
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
  ISBN varchar(13) PRIMARY KEY, -- stores ISBN of book
  Author varchar(20) NOT NULL, -- Author attribute to be made as foreign key of Author table later in file
  Title varchar(40) NOT NULL, -- Title of the book
  DateReleased date NOT NULL, -- Release date of the book
  Description varchar(255) NOT NULL, -- Description about the book
  Picture varchar(255) -- A picture of the book
);

CREATE TABLE author (
  Name varchar(20) PRIMARY KEY, -- Name of the author
  DOB date NOT NULL -- Date of birth of the author
);

CREATE TABLE pages (
  ID int PRIMARY KEY AUTO_INCREMENT, -- Unique primary key for the total pages and pages read
  Page int NOT NULL, -- Current page the user is on
  TotalPages int NOT NULL -- Total pages of the book
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
  User varchar(10), -- User attribute to be foreign key of Email attribute in users table later in file
  CommentID int NOT NULL, -- CommentID to be foreign key of ID in comments table later in file
  ReviewID int NOT NULL, -- ReviewID to be foreign key of ID in reviews table later in file
  created_at datetime DEFAULT (now()) -- created_at attribute is supposed to set to the current time when data is inserted to other attributes by default
);

CREATE TABLE premium (
  Email varchar(35) NOT NULL, -- Email to be set as foreign key in table that acts like subtype table to supertype table: 'users'
  BadgeURL varchar(255), -- BadgeURL stores the URL of badge user of user
  BackgroundURL varchar(255) -- BackgroundURL attribute stores the background url for the website
);

CREATE TABLE standard (
  Email varchar(35) NOT NULL, -- Email to be set as foreign key in table that acts like subtype table to supertype table: 'users'
  BooksLimit int DEFAULT(5), -- BooksLimit attribute is the amount of book reviews that can be made, by default the value should be set to 5 
  PrivateReviews int DEFAULT(2), -- PrivateReviews attribute is the amount of private book reviews that can be posted should be default to 2
  CHECK (BooksLimit BETWEEN 0 and 5), -- BooksLimit must be between 0 and 5
  CHECK (PrivateReviews BETWEEN 0 and 2) -- PrivateReviews must be between 0 and 2
);

/*
  Composite key (Email, ReviewID) created as favourites both referencing the foreign keys of the 'users' and 'reviews' table
  allowing a link to be created
*/
CREATE TABLE favourites (
  Email varchar(35) NOT NULL, 
  ReviewID int NOT NULL,
  PRIMARY KEY(Email, ReviewID),
  FOREIGN KEY (Email) REFERENCES users(Email),
  FOREIGN KEY (ReviewID) REFERENCES reviews(ID)
);


/*
  Use ALTER TABLE to add foreign keys to tables making it easier to create and change tables if needed 
  in different orders without needing to worry about invalid constraints
*/

ALTER TABLE users_reviews ADD FOREIGN KEY (Email) REFERENCES users (Email);

ALTER TABLE users_reviews ADD FOREIGN KEY (ReviewID) REFERENCES reviews (ID);

ALTER TABLE users ADD FOREIGN KEY (Username) REFERENCES profile (Username);

ALTER TABLE reviews ADD FOREIGN KEY (ISBN) REFERENCES books (ISBN);

ALTER TABLE books ADD FOREIGN KEY (Author) REFERENCES author (Name);

ALTER TABLE users_reviews ADD FOREIGN KEY (PageID) REFERENCES pages (ID);

ALTER TABLE posts ADD FOREIGN KEY (CommentID) REFERENCES comments (ID);

ALTER TABLE posts ADD FOREIGN KEY (ReviewID) REFERENCES reviews (ID);

ALTER TABLE posts ADD FOREIGN KEY (User) REFERENCES users (Email);

ALTER Table premium ADD FOREIGN KEY (Email) REFERENCES users (Email);

ALTER Table standard ADD FOREIGN KEY (Email) REFERENCES users (Email);