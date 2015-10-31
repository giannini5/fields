use fields;

select * from league;
select * from season;
select * from division where leagueId = 1 order by id;
select * from team;
select * from coach;
select * from session;
select * from facility;
select * from field;
select * from reservation;
select * from location;
select * from facilityLocation;
select * from divisionField;
select * from practiceFieldCoordinator;

select * from field as f join facility as l on l.id = f.facilityId join league as g on g.id = l.leagueId and g.id = 1;

insert into league (name) values ('AYSO Region 122');
insert into practiceFieldCoordinator (leagueId, email, name, password) values (1, 'dave@giannini5.com', 'David Giannini', '1234');

insert into location (leagueId, name) values (1, 'Goleta');
insert into location (leagueId, name) values (1, 'Santa Barbara');
insert into location (leagueId, name) values (1, 'Montecito');
insert into location (leagueId, name) values (1, 'Between Santa Barbara and Goleta');

insert into season (leagueId, name, enabled) values (1, 'Fall 2015', 1);
insert into facility (leagueId, name, address1, address2, city, state, postalCode, country, contactName, contactEmail, contactPhone, image, enabled) values
    (1, 'Girsh Park', '2910 Paseo del Refugio', '', 'Santa Barbara', 'CA', '93105', 'USA', 'David Giannini', 'ayso122Fields@gmail.com', '8052523944', 'GirshParkFields_zps37ac68d3.jpg', 1);
insert into facility (leagueId, name, address1, address2, city, state, postalCode, country, contactName, contactEmail, contactPhone, image, enabled) values
    (1, 'Mountain View Elementary', '2911 Paseo del Refugio', '', 'Santa Barbara', 'CA', '93101', 'USA', 'David Giannini', 'ayso122Fields@gmail.com', '8052523944', 'MountainViewElementaryFields_zpsdc6bb75a.jpg', 1);

insert into facilityLocation (facilityId, locationId) values (1, 1);
insert into facilityLocation (facilityId, locationId) values (2, 1);
insert into facilityLocation (facilityId, locationId) values (2, 4);

insert into field (facilityId, name, enabled) values (1, 'Field A', 1);
insert into field (facilityId, name, enabled) values (1, 'Field B', 1);
insert into field (facilityId, name, enabled) values (1, 'Field C', 1);
insert into field (facilityId, name, enabled) values (1, 'Field D', 1);
insert into field (facilityId, name, enabled) values (1, 'Field E', 1);
insert into field (facilityId, name, enabled) values (1, 'Field F', 1);
insert into field (facilityId, name, enabled) values (1, 'Field G', 1);
insert into field (facilityId, name, enabled) values (1, 'Field H', 1);

insert into field (facilityId, name, enabled) values (2, 'Field 1A', 1);
insert into field (facilityId, name, enabled) values (2, 'Field 1B', 1);
insert into field (facilityId, name, enabled) values (2, 'Field 1C', 1);
insert into field (facilityId, name, enabled) values (2, 'Field 1D', 1);

insert into division (leagueId, name, enabled) values (1, 'U5', 1);
insert into division (leagueId, name, enabled) values (1, 'U6', 1);
insert into division (leagueId, name, enabled) values (1, 'U7', 1);
insert into division (leagueId, name, enabled) values (1, 'U8', 1);
insert into division (leagueId, name, enabled) values (1, 'U9', 1);
insert into division (leagueId, name, enabled) values (1, 'U10', 1);
insert into division (leagueId, name, enabled) values (1, 'U11', 1);
insert into division (leagueId, name, enabled) values (1, 'U12', 1);
insert into division (leagueId, name, enabled) values (1, 'U14', 1);
insert into division (leagueId, name, enabled) values (1, 'U16-19', 1);

insert into divisionField (divisionId, facilityId, fieldId) values (6, 1, 1); -- Girsh A
insert into divisionField (divisionId, facilityId, fieldId) values (6, 1, 2); -- Girsh B
insert into divisionField (divisionId, facilityId, fieldId) values (6, 1, 3); -- Girsh C
insert into divisionField (divisionId, facilityId, fieldId) values (7, 1, 1); -- Girsh A
insert into divisionField (divisionId, facilityId, fieldId) values (7, 1, 2); -- Girsh B
insert into divisionField (divisionId, facilityId, fieldId) values (7, 1, 3); -- Girsh C

insert into divisionField (divisionId, facilityId, fieldId) values (8, 1, 1); -- Girsh A
insert into divisionField (divisionId, facilityId, fieldId) values (8, 1, 2); -- Girsh B
insert into divisionField (divisionId, facilityId, fieldId) values (8, 1, 3); -- Girsh C

insert into divisionField (divisionId, facilityId, fieldId) values (9, 1, 1); -- Girsh A
insert into divisionField (divisionId, facilityId, fieldId) values (9, 1, 2); -- Girsh B
insert into divisionField (divisionId, facilityId, fieldId) values (9, 1, 3); -- Girsh C

insert into divisionField (divisionId, facilityId, fieldId) values (10, 1, 1); -- Girsh A
insert into divisionField (divisionId, facilityId, fieldId) values (10, 1, 2); -- Girsh B
insert into divisionField (divisionId, facilityId, fieldId) values (10, 1, 3); -- Girsh C

insert into divisionField (divisionId, facilityId, fieldId) values (1, 1, 4); -- Girsh D
insert into divisionField (divisionId, facilityId, fieldId) values (1, 1, 5); -- Girsh E
insert into divisionField (divisionId, facilityId, fieldId) values (1, 1, 6); -- Girsh F
insert into divisionField (divisionId, facilityId, fieldId) values (2, 1, 4); -- Girsh D
insert into divisionField (divisionId, facilityId, fieldId) values (2, 1, 5); -- Girsh E
insert into divisionField (divisionId, facilityId, fieldId) values (2, 1, 6); -- Girsh F

insert into divisionField (divisionId, facilityId, fieldId) values (7, 1, 8); -- Girsh G
insert into divisionField (divisionId, facilityId, fieldId) values (7, 1, 9); -- Girsh H
insert into divisionField (divisionId, facilityId, fieldId) values (8, 1, 8); -- Girsh G
insert into divisionField (divisionId, facilityId, fieldId) values (8, 1, 9); -- Girsh H
insert into divisionField (divisionId, facilityId, fieldId) values (9, 1, 8); -- Girsh G
insert into divisionField (divisionId, facilityId, fieldId) values (9, 1, 9); -- Girsh H

insert into divisionField (divisionId, facilityId, fieldId) values (5, 2, 9); -- Mountain View 1A
insert into divisionField (divisionId, facilityId, fieldId) values (5, 2, 10); -- Mountain View 1B
insert into divisionField (divisionId, facilityId, fieldId) values (6, 2, 9); -- Mountain View 1A
insert into divisionField (divisionId, facilityId, fieldId) values (6, 2, 10); -- Mountain View 1B
insert into divisionField (divisionId, facilityId, fieldId) values (7, 2, 9); -- Mountain View 1A
insert into divisionField (divisionId, facilityId, fieldId) values (7, 2, 10); -- Mountain View 1B
insert into divisionField (divisionId, facilityId, fieldId) values (8, 2, 9); -- Mountain View 1A
insert into divisionField (divisionId, facilityId, fieldId) values (8, 2, 10); -- Mountain View 1B
insert into divisionField (divisionId, facilityId, fieldId) values (3, 2, 11); -- Mountain View 1C
insert into divisionField (divisionId, facilityId, fieldId) values (3, 2, 12); -- Mountain View 1D
insert into divisionField (divisionId, facilityId, fieldId) values (4, 2, 11); -- Mountain View 1C
insert into divisionField (divisionId, facilityId, fieldId) values (4, 2, 12); -- Mountain View 1D
