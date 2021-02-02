# PwPop

This webapp is the final exercise for the subject _Web Proyects II_ at _La Salle Barcelona - URL_ taught by [Jaume Capdevila Pérez](https://www.salleurl.edu/es/jaume-capdevila-perez).
The objective of this final exercise is to create a plataform to sell second-hand objects like [Wallapop](https://es.wallapop.com/).
This platform was developed by:
* [Noel Daniel Aguilera Terrazas](https://gitlab.com/daguilera3220).
* [Victor Garrido Martínez](https://gitlab.com/ls31300).
* [Luis Angel Ortega Holguin](https://gitlab.com/LinkSake).

## Getting Started

### Prerequisites

 * Git
 * PHP >= 7.0
 * Composer
 * Homestead
 * MySQL

### Installing

1. Clone the project to your _code_ folder (or equivalent) in Homestead/Vagrant VM.

__Clone with SSH__

```bash
git@gitlab.com:daguilera3220/pwpop.git
```

__Or clone with HTTPS__
```bash
https://gitlab.com/daguilera3220/pwpop.git
```

2. Go to the _pwpop_ folder that you just clone (on your VM) and run
```bash
composer install
```
This will install all the dependencies of the project.

3. Open MySQL and copy:
```sql
CREATE TABLE `category` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
LOCK TABLES `category` WRITE;
INSERT INTO `category` (`id`, `name`)
VALUES
	(1,'Computers and Electronic'),
	(2,'Cars'),
	(3,'Sports'),
	(4,'Games'),
	(5,'Fashion'),
	(6,'Home'),
	(7,'Other');
UNLOCK TABLES;

CREATE TABLE `favorites` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned NOT NULL,
  `product_id` int(11) unsigned NOT NULL,
  `created_at` date NOT NULL,
  `updated_at` date NOT NULL,
  PRIMARY KEY (`id`),
  KEY `product_is_favorite` (`product_id`),
  KEY `user_has_favorites` (`user_id`),
  CONSTRAINT `product_is_favorite` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`),
  CONSTRAINT `user_has_favorites` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `image_product` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(11) unsigned NOT NULL,
  `image` varchar(100) NOT NULL DEFAULT '',
  `updated_at` date NOT NULL,
  `created_at` date NOT NULL,
  PRIMARY KEY (`id`),
  KEY `image-product` (`product_id`),
  CONSTRAINT `image-product` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `product` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(50) DEFAULT NULL,
  `description` text,
  `price` double DEFAULT NULL,
  `category_id` int(11) unsigned NOT NULL,
  `user_id` int(11) unsigned NOT NULL,
  `updated_at` date NOT NULL,
  `created_at` date NOT NULL,
  `deleted_at` date DEFAULT NULL,
  `sold_out` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `title` (`title`),
  KEY `product_category` (`category_id`),
  KEY `product_user` (`user_id`),
  CONSTRAINT `product_category` FOREIGN KEY (`category_id`) REFERENCES `category` (`id`),
  CONSTRAINT `product_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `user` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) DEFAULT NULL,
  `username` varchar(20) DEFAULT NULL,
  `email` varchar(50) DEFAULT NULL,
  `birthdate` date DEFAULT NULL,
  `phone` int(11) DEFAULT NULL,
  `password` varchar(50) DEFAULT NULL,
  `image` varchar(100) DEFAULT NULL,
  `validated` int(1) DEFAULT NULL,
  `validation_code` int(8) DEFAULT NULL,
  `updated_at` date DEFAULT NULL,
  `created_at` date DEFAULT NULL,
  `session_id` int(8) DEFAULT NULL,
  `is_active` int(1) DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
```
or open your favoirte database management application for MySQL and import the `pwpop_db.sql` file that is on root.

4. Go to your `Homestead.yaml` and add
```yaml
    - map: pwpop.test
      to: /home/vagrant/code/pwpop/public
```
5. Edit your host to make sure that you will be able to open your VM

> Pro tip: This can be done in Mac with `sudo nano /etc/hosts/`

## Work Distribution
The project was divided in 12 blocks, and those were divided among the team members in the following order:

* __Noel Daniel Aguilera__ made the blocks _1_, _2_, _3_, _10.1_, _10.2_.
* __Victor Garrido__ was in charge of the blocks _4_, _5_, _6_, _7_.
* __Luis Angel Ortega__ implemented the blocks _8_, _9_, _11_, _12_.

> A big thanks to Noel Daniel Aguilera, who helped all of us in the frontend area.

## Hours Worked
A multiple quantity of hours were necessary to make this project, below is each team member estimated guess and the estimated total that were needed.

* _Noel Daniel Aguilera_ - 29 hours
* _Victor Garrido_ - 20 hours
* _Luis Angel Ortega_ - 21 hours

Making a total of __70__ hours to complete the project.

## Class Diagram
Below is the class diagram of the data base used in this project (as 18/05/2019).

![Image](https://i.imgur.com/GqgPU65.jpg)

## Why MD5?
[MD5](https://en.wikipedia.org/wiki/MD5) (Message-Digest Algorithm 5) makes a hash to the given string, in this case the password, and returns a series of numbers that can be only interpreted by the algoritm. This has proven to be secure for small apps like this one and this algorithm also was choosen because it's among the most popular cypher algorithms between developers.

## Built With
* [Slim](https://www.slimframework.com/) - A micro framework for PHP.
* [Twig](https://twig.symfony.com/) - A modern template engine for PHP.
* [Bootstrap](https://getbootstrap.com/) - An open source toolkit for developing with HTML, CSS, and JS.
* [Shards](https://designrevision.com/demo/shards/) - A design system based on Bootstrap 4.
* [Slick](https://kenwheeler.github.io/slick/) - The carousels used on this project.
* [Tachyons](https://tachyons.io/) - A CSS library that focuses on using as little css as possible.
* [PHPMailer](https://github.com/PHPMailer/PHPMailer) - The classic email sending library for PHP.
* [Eloquent](https://laravel.com/docs/5.8/eloquent) - A simple ActiveRecord implementation for working with the database.
* [FIG Cookies](https://github.com/dflydev/dflydev-fig-cookies) - A cookie manager for PSR-7 requests and responses.
* [Iconsvg](https://iconsvg.xyz/) - A SVG icon library.
* [Error Pages Sketch Resource](https://www.sketchappsources.com/free-source/2265-sample-error-page-designs-sketch-freebie-resource.html) - Freebie templates for error pages.

## License
This project is licensed under the [MIT](https://choosealicense.com/licenses/mit/) License

## Acknowledgments
Thanks to Jaume for being our teacher and inspiration. Without this class we would not have learn a lot of things that are useful for us now and on the future (not just PHP).