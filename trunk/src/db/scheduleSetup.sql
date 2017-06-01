select * from league;
select * from season;
select * from division;
select * from scheduleCoordinator;
select * from team;
select * from coach;
select * from assistantCoach;
select * from player limit 10;
select * from family;
select * from gameDate;
select * from divisionField;
select * from schedule;
select * from pool;
select * from game;
select * from team where poolId is not null;
select * from gameTime;
select * from facility;
select * from field;
select count(1) from familyGame;

select * from division; -- u10G 1
select * from schedule where divisionId = 1;
select * from flight where scheduleId = 90;
select * from pool where flightId = 105;
select * from team where poolId = 347;

select familyId, count(1) from familyGame group by 1;
select * from coach where name like "%Brennan%";
select * from familyGame where familyId = 19;
-- Fix overlap lost family games!!! Worked, but FamilyGames deleted???

select * from gameTime where gameId is not null;
select * from division where name = 'U5';
select * from schedule where divisionId = 34;
select * from pool where scheduleId = 590;
select * from game where poolId = 592;
select * from gameTime where id in (1, 2, 3, 4);
select * from field where id = 1;

insert into league (name) values ('AYSO Region 122');
insert into scheduleCoordinator (leagueId, email, name, password) values (1, 'dave@giannini5.com', 'David Giannini', '1234');
insert into scheduleCoordinator (leagueId, email, name, password) values (1, 'admin', 'Admin', 'admin');

truncate table assistantCoach;
truncate table coach;
truncate table division;
truncate table divisionField;
truncate table family;
truncate table facility;
truncate table field;
truncate table game;
truncate table gameDate;
truncate table gameTime;
truncate table league;
truncate table player;
truncate table pool;
truncate table schedule;
truncate table scheduleCoordinator;
truncate table season;
truncate table team;
truncate table familyGame;
truncate table flight;

select * from game where id = 13;
