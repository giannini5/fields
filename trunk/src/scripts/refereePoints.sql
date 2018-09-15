-- create database referee;
use referee;

drop table if exists game;
create table game
(
    region          varchar(10),
    refereeName     varchar(64),
    day             date,
    maxGamesPerTeam int,
    unused1         int,
    count           int,
    unused2         int,
    
    primary key (refereeName, day)
);

load data infile "/tmp/refereeCounts.csv"
    into table game
    fields terminated by ','
    lines terminated by '\r'
    ignore 1 lines;

drop table if exists rawRefereesByTeam;
create table rawRefereesByTeam
(
    division      varchar(20),
    teamNumber    int,
    teamId        varchar(20),
    coachName     varchar(64),
    refereeName1  varchar(64),
    refereeName2  varchar(64),
    refereeName3  varchar(64),
    refereeName4  varchar(64),
    refereeName5  varchar(64),
    refereeName6  varchar(64),
    refereeName7  varchar(64),
    refereeName8  varchar(64),
    refereeName9  varchar(64),
    refereeName10 varchar(64),
    
    unique key ux_ref1 (teamId, refereeName1),
    unique key ux_ref2 (teamId, refereeName2),
    unique key ux_ref3 (teamId, refereeName3),
    unique key ux_ref4 (teamId, refereeName4),
    unique key ux_ref5 (teamId, refereeName5),
    unique key ux_ref6 (teamId, refereeName6),
    unique key ux_ref7 (teamId, refereeName7),
    unique key ux_ref8 (teamId, refereeName8),
    unique key ux_ref9 (teamId, refereeName9),
    unique key ux_ref10 (teamId, refereeName10),
    
    index ix_refereeName1 (refereeName1),
    index ix_refereeName2 (refereeName2),
    index ix_refereeName3 (refereeName3),
    index ix_refereeName4 (refereeName4),
    index ix_refereeName5 (refereeName5),
    index ix_refereeName6 (refereeName6),
    index ix_refereeName7 (refereeName7),
    index ix_refereeName8 (refereeName8),
    index ix_refereeName9 (refereeName9),
    index ix_refereeName10 (refereeName10)
);

load data infile "/tmp/refereesByTeam.csv"
    into table rawRefereesByTeam
    fields terminated by ','
    lines terminated by '\r'
    ignore 1 lines;

drop table if exists team;
create table team
(
    division    varchar(20),
    teamNumber  int,
    teamId      varchar(20),
    coachName   varchar(64),
    
    primary key (teamId)
);

insert into team
    select division, teamNumber, teamId, coachName from rawRefereesByTeam;

drop table if exists refereeByTeam;
create table refereeByTeam
(
    teamId      varchar(20),
    refereeName varchar(64),
    
    primary key (teamId, refereeName)
);

insert into refereeByTeam
    select teamId, refereeName1 from rawRefereesByTeam where refereeName1 != '';
insert into refereeByTeam
    select teamId, refereeName2 from rawRefereesByTeam where refereeName2 != '';
insert into refereeByTeam
    select teamId, refereeName3 from rawRefereesByTeam where refereeName3 != '';
insert into refereeByTeam
    select teamId, refereeName4 from rawRefereesByTeam where refereeName4 != '';
insert into refereeByTeam
    select teamId, refereeName5 from rawRefereesByTeam where refereeName5 != '';
insert into refereeByTeam
    select teamId, refereeName6 from rawRefereesByTeam where refereeName6 != '';
insert into refereeByTeam
    select teamId, refereeName7 from rawRefereesByTeam where refereeName7 != '';
insert into refereeByTeam
    select teamId, refereeName8 from rawRefereesByTeam where refereeName8 != '';
insert into refereeByTeam
    select teamId, refereeName9 from rawRefereesByTeam where refereeName9 != '';
insert into refereeByTeam
    select teamId, refereeName10 from rawRefereesByTeam where refereeName10 != '';

drop table if exists refereeTeamCount;
create table refereeTeamCount
(
    refereeName varchar(64),
    teamCount   int,
    
    primary key (refereeName)
);

insert into refereeTeamCount (refereeName, teamCount)
    select
        refereeName,
        count(1) as teamCount
    from
        refereeByTeam
    group by
        1;
-- select * from refereeTeamCount order by teamCount desc;

drop table if exists refereeGameCount;
create table refereeGameCount
(
    refereeName varchar(64),
    games       int,
    
    primary key (refereeName)
);

insert into refereeGameCount (refereeName, games)
    select
        refereeName,
        sum(count) as games
    from
        game
    group by
        1;
-- select * from refereeGameCount order by gameCount desc;

drop table if exists gamesByTeam;
create table gamesByTeam
(
    teamId      varchar(20),
    gameDate    date,
    gameCount   float,
    
    primary key (teamId, gameDate)
);

-- game:             for each referee, number of games ref'd that day and maxGamesAllowed
-- team:             basic team info
-- refereeByTeam:    Team's assinged referees
-- refereeTeamCount: Count of teams referee is helping
--
-- Rule: Team can earn at most maxGamesPerDay
-- Rule: Referee can work many games, games are divided by number of teams they support

-- Referee'd games counted for team on given day
insert into gamesByTeam (teamId, gameDate, gameCount)
    select
        data.teamId,
        data.day,
        case when data.maxGamesPerTeam > data.gameCount then data.gameCount else data.maxGamesPerTeam end as gameCount
    from
        (
            -- Games per day for team and capture maxGamesPerTeam allowed for that day
            select
                refereesByTeamByDay.teamId,
                refereesByTeamByDay.day,
                max(refereesByTeamByDay.maxGamesPerTeam) as maxGamesPerTeam,
                sum(refereesByTeamByDay.gamesForTeam) as gameCount
            from
                (
                    -- Games for team by day, by referee, with percent split for refs helping multiple teams
                    select
                        t.teamId,
                        g.day,
                        g.maxGamesPerTeam,
                        r.refereeName,
                        round(g.count / c.teamCount, 1) as gamesForTeam
                    from
                        team as t
                        join refereeByTeam as r on
                            r.teamId = t.teamId
                        join game as g on
                            g.refereeName = r.refereeName
                        join refereeTeamCount as c on
                            c.refereeName = r.refereeName
                ) as refereesByTeamByDay
            group by
                1, 2
        ) as data;


/* Games for Team */
select
    t.teamId,
    t.coachName,
    sum(ifnull(gameCount, 0)) as totalGamesRefereed,
    case when 20 - sum(ifnull(gameCount, 0)) > 0 then
        20 - sum(ifnull(gameCount, 0)) else 0 end as gamesNeededToQualifyForTournament
from
    team as t
    left outer join gamesByTeam as g on
        g.teamId = t.teamId
group by
    1, 2
order by
    1;

/* Games By Day for Team */
select
    t.teamId,
    t.coachName,
    gDate.gameDate as gameWeekend,
    sum(ifnull(g.gameCount, 0)) as gamesRefereed
from
    team as t
    join (select distinct gameDate from gamesByTeam) as gDate
    left outer join gamesByTeam as g on
        g.teamId = t.teamId
        and g.gameDate = gDate.gameDate
group by
    1, 2, 3
order by
    1, 2, 3;

/* Games By Day for Referee */
select
    r.refereeName,
    gDate.gameDate,
    ifnull(count, 0) as gamesRefereed,
    r.teamCount as countOfTeams,
    round(ifnull(count, 0) / r.teamCount, 1) as gamesPerTeam
from
    refereeTeamCount as r
    join (select distinct gameDate from gamesByTeam) as gDate
    left outer join game as g on
        g.refereeName = r.refereeName
        and g.day = gDate.gameDate
order by
    1;
