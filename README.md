# Programming Exam Website

This is the **model** portion of a programming exam website that I developed as part of my Software Design and Engineering course that I took at the **New Jersey Institute of Technology** during my second year. I worked on a team of three developers, and was personally tasked with the design and implementation of the MySQL database, as well as the model, which serves as a layer between the controller and the database. 

The purpose of the website is to allow teachers to make python-based programming exams, and allow students in their class to take those exams. An autograder (part of the controller) then runs the student's code in a sandbox environment, and tests their methods against teacher specified test cases. The autograder grades the exams, and uses the model to insert the grades into the database.

## Deployment

The website can be visited at [https://web.njit.edu/~nya2/](https://web.njit.edu/~nya2/).

The teacher login credentials:

    username: teacher
    password: password

The student login credentials:

    username: Draco Malfoy
    password: password

## Built With

PHP

MySQL

## Authors

* **Tadayoshi Carvajal** - model - [TadayoshiCarvajal](https://github.com/TadayoshiCarvajal)
* **Nikolay Avramov** - view - [nyavramov](https://github.com/nyavramov)
* **Jae Young Choi** - controller

