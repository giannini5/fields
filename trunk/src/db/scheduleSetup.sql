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
insert into league (name) values ('AYSO Region 122');
insert into scheduleCoordinator (leagueId, email, name, password) values (1, 'dave@giannini5.com', 'David Giannini', '1234');
insert into scheduleCoordinator (leagueId, email, name, password) values (1, 'geoffayso122@gmail.com', 'Geoff Friedman', '1234');
insert into scheduleCoordinator (leagueId, email, name, password) values (1, 'admin', 'Admin', 'admin');

-- Dump games for game scoring
-- One row per team - use Excel pivot for formatting
select
    d.day as gameDay,
    g.id as gameId,
    left(t.startTime, 5) as Start,
    concat(i.name, " ", i.gender) as division,
    c.name as facility,
    f.name as field,
    concat("H: ", h.nameId, " ", hc.name) as team
from
    game as g
    join gameTime as t on
        t.id = g.gameTimeId
    join gameDate as d on
        d.id = t.gameDateId
    join team as h on
        h.id = g.homeTeamId
    join coach as hc on
        hc.teamId = h.id
    join division as i on
        i.id = h.divisionId
    join field as f on
        f.id = t.fieldId
    join facility as c on
        c.id = f.facilityId
union
select
    d.day as gameDay,
    g.id as gameId,
    left(t.startTime, 5) as Start,
    concat(i.name, " ", i.gender) as division,
    c.name as facility,
    f.name as field,
    concat("V: ", v.nameId, " ", vc.name) as team
from
    game as g
    join gameTime as t on
        t.id = g.gameTimeId
    join gameDate as d on
        d.id = t.gameDateId
    join team as v on
        v.id = g.visitingTeamId
    join coach as vc on
        vc.teamId = v.id
    join division as i on
        i.id = v.divisionId
    join field as f on
        f.id = t.fieldId
    join facility as c on
        c.id = f.facilityId;

/*
1001    RS U14 Standby
921     Storke U12 Only Standby
1084    Girsh U12 Standby
802     Girsh U10G Standby
961     Girsh U10B Standby
 */

