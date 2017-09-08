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
-- insert into scheduleCoordinator (leagueId, email, name, password) values (1, 'admin', 'Admin', 'admin');

select * from family where phone2 = '805-284-4970';
select * from coach where name like '%Roden%';

select * from familyGame where familyId = 35;
select * from coach where familyId = 35;
select * from game where id = 971;

insert into family (seasonId, phone2) values (1, '805-284-4970');
update coach set familyId = 35 where id in (22, 208);
insert into familyGame (familyId, gameId)
    select 35, id from game where homeTeamId in (22, 208) or visitingTeamId in (22, 208);

select
    data.Date,
    data.Start,
    data.End,
    data.thirdPartyFieldId,
    concat('122-', data.division, "-", data.homeTeamNumber) as homeTeam,
    concat('122-', data.division, "-", data.visitingTeamNumber) as visitingTeam
from (
    select
        date_format(d.day, "%c/%e/%Y") as Date,
        left(t.startTime, 5) as Start,
        left(addtime(t.startTime,
                case when gameDurationMinutes = 60 then "01:00:00"
                     when gameDurationMinutes = 75 then "01:15:00"
                     when gameDurationMinutes = 90 then "01:30:00"
                     else "error" end),
            5) as End,
        f.thirdPartyFieldId,
        case when right(h.nameId, 2) between "01" and "09" then right(h.nameId, 1) else right(h.nameId, 2) end as homeTeamNumber,
        case when right(v.nameId, 2) between "01" and "09" then right(v.nameId, 1) else right(v.nameId, 2) end as visitingTeamNumber,
        concat("U", left(i.name, length(i.name) - 1),
            case when i.gender = 'Boys' then 'B' else 'G' end) as division
    from
        game as g
        join gameTime as t on
            t.id = g.gameTimeId
        join gameDate as d on
            d.id = t.gameDateId
        join team as h on
            h.id = g.homeTeamId
        join team as v on
            v.id = g.visitingTeamId
        join division as i on
            i.id = h.divisionId
            and i.name = "10U"
        join field as f on
            f.id = t.fieldId
    ) as data
into outfile "/Users/dag/ayso/schedule_3.txt" LINES TERMINATED BY '\n';

/*
1001    RS U14 Standby
921     Storke U12 Only Standby
1084    Girsh U12 Standby
802     Girsh U10G Standby
961     Girsh U10B Standby
 */
