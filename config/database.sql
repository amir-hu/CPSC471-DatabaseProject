DROP DATABASE IF EXISTS ChildDaycare;

CREATE DATABASE ChildDaycare;

USE ChildDaycare;

CREATE TABLE IF NOT EXISTS DAYCARE (
     DaycareName VARCHAR(100) NOT NULL
   , DaycareAddress VARCHAR(100) NOT NULL
   , TotalNumOfCaretakers INT
   , CONSTRAINT PK_Daycare PRIMARY KEY (DaycareName, DaycareAddress)
);

CREATE TABLE IF NOT EXISTS PERSON (
     SIN INT NOT NULL
   , Name VARCHAR(30)
   , Gender VARCHAR(30)
   , Address VARCHAR(50)
   , Phone INT
   , StartDate DATE
   , PRIMARY KEY (SIN)
);