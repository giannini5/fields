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
    status      char(1) default 'A',
    
    primary key (teamId, refereeName)
);

insert into refereeByTeam
    select
        teamId,
        case when left(refereeName1, 2) = "N:" then right(refereeName1, length(refereeName1) - 2) else refereeName1 end as refereeName,
        case when left(refereeName1, 2) = "N:" then 'N' else 'A' end as status
    from rawRefereesByTeam 
    where refereeName1 != '';
insert into refereeByTeam
    select
        teamId,
        case when left(refereeName2, 2) = "N:" then right(refereeName2, length(refereeName2) - 2) else refereeName2 end as refereeName,
        case when left(refereeName2, 2) = "N:" then 'N' else 'A' end as status
    from rawRefereesByTeam 
    where refereeName2 != '';
insert into refereeByTeam
    select
        teamId,
        case when left(refereeName3, 2) = "N:" then right(refereeName3, length(refereeName3) - 2) else refereeName3 end as refereeName,
        case when left(refereeName3, 2) = "N:" then 'N' else 'A' end as status
    from rawRefereesByTeam 
    where refereeName3 != '';
insert into refereeByTeam
    select
        teamId,
        case when left(refereeName4, 2) = "N:" then right(refereeName4, length(refereeName4) - 2) else refereeName4 end as refereeName,
        case when left(refereeName4, 2) = "N:" then 'N' else 'A' end as status
    from rawRefereesByTeam 
    where refereeName4 != '';
insert into refereeByTeam
    select
        teamId,
        case when left(refereeName5, 2) = "N:" then right(refereeName5, length(refereeName5) - 2) else refereeName5 end as refereeName,
        case when left(refereeName5, 2) = "N:" then 'N' else 'A' end as status
    from rawRefereesByTeam 
    where refereeName5 != '';
insert into refereeByTeam
    select
        teamId,
        case when left(refereeName6, 2) = "N:" then right(refereeName6, length(refereeName6) - 2) else refereeName6 end as refereeName,
        case when left(refereeName6, 2) = "N:" then 'N' else 'A' end as status
    from rawRefereesByTeam 
    where refereeName6 != '';
insert into refereeByTeam
    select
        teamId,
        case when left(refereeName7, 2) = "N:" then right(refereeName7, length(refereeName7) - 2) else refereeName7 end as refereeName,
        case when left(refereeName7, 2) = "N:" then 'N' else 'A' end as status
    from rawRefereesByTeam 
    where refereeName7 != '';
insert into refereeByTeam
    select
        teamId,
        case when left(refereeName8, 2) = "N:" then right(refereeName8, length(refereeName8) - 2) else refereeName8 end as refereeName,
        case when left(refereeName8, 2) = "N:" then 'N' else 'A' end as status
    from rawRefereesByTeam 
    where refereeName9 != '';
insert into refereeByTeam
    select
        teamId,
        case when left(refereeName9, 2) = "N:" then right(refereeName9, length(refereeName9) - 2) else refereeName9 end as refereeName,
        case when left(refereeName9, 2) = "N:" then 'N' else 'A' end as status
    from rawRefereesByTeam 
    where refereeName9 != '';
insert into refereeByTeam
    select
        teamId,
        case when left(refereeName10, 2) = "N:" then right(refereeName10, length(refereeName10) - 2) else refereeName10 end as refereeName,
        case when left(refereeName10, 2) = "N:" then 'N' else 'A' end as status
    from rawRefereesByTeam 
    where refereeName10 != '';
    
-- select status, count(1) from refereeByTeam group by 1;

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

drop table if exists refereeStatusByTeam;
create table refereeStatusByTeam
(
    teamId             varchar(20),
    refereesWithStatus   varchar(1024),
    
    primary key (teamId)
);

insert into refereeStatusByTeam (teamId, refereesWithStatus)
    select
        t.teamId,
        case when t.status != 'A' then concat(t.refereeName, " (NOT YET APPROVED)")
             when r.teamCount > 1 then concat(t.refereeName, " (Helping ", r.teamCount, " teams)")
             else t.refereeName end as refereesWithStatus
    from
        refereeByTeam as t
        join refereeTeamCount as r on
            r.refereeName = t.refereeName
on duplicate key
    update refereesWithStatus = concat(refereesWithStatus, ", ",
        case when t.status != 'A' then concat(t.refereeName, " (NOT YET APPROVED)")
             when r.teamCount > 1 then concat(t.refereeName, " (Helping ", r.teamCount, " teams)")
             else t.refereeName end);

/* Games for Team */
select
    t.teamId,
    t.coachName,
    sum(ifnull(gameCount, 0)) as totalGamesRefereed,
    case when 5 - sum(ifnull(gameCount, 0)) > 0 then
        5 - sum(ifnull(gameCount, 0)) else 0 end as gamesNeededForRefereeBonus,
    case when 18 - sum(ifnull(gameCount, 0)) > 0 then
        18 - sum(ifnull(gameCount, 0)) else 0 end as gamesNeededToQualifyForTournament
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
    g.refereeName,
    gDate.gameDate,
    ifnull(count, 0) as gamesRefereed,
    ifnull(r.teamCount, 0) as countOfTeams,
    case when r.teamCount is null then 0 else round(ifnull(count, 0) / r.teamCount, 1) end as gamesPerTeam
from
    (select distinct gameDate from gamesByTeam) as gDate
    left outer join game as g on
        g.day = gDate.gameDate
    left outer join refereeTeamCount as r on
        r.refereeName = g.refereeName
order by
    1;

/* Referee Status by Team */
select
    t.teamId,
    t.coachName as coach,
    ifnull(r.refereesWithStatus, "No referees assigned to team") as refereesWithStatus
from
    team as t
    left outer join refereeStatusByTeam as r on
        r.teamId = t.teamId
order by
    teamId;

select
    data.refereeName
from
(
    select
        g.refereeName
    from
        game as g
        left outer join refereeTeamCount as r on
            r.refereeName = g.refereeName
    union
    select
        r.refereeName
    from
        refereeTeamCount as r
        left outer join game as g on
            g.refereeName = r.refereeName
) as data
group by 1
order by 1;
