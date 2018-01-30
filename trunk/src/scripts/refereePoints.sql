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

drop table if exists team;
create table team
(
    division    varchar(20),
    teamNumber  int,
    teamId      varchar(20),
    coachName   varchar(64),
    refereeName varchar(64),
    
    primary key (teamId, refereeName),
    index ix_refereeName (refereeName)
);

load data infile "/tmp/refereesByTeam.csv"
    into table team
    fields terminated by ','
    lines terminated by '\r'
    ignore 1 lines;

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
        team
    where
        refereeName != ''
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
    where
        refereeName != ''
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

insert into gamesByTeam (teamId, gameDate, gameCount)
    select
        g.teamId,
        g.day,
        sum(round(g.gameCount / r.teamCount, 1)) as gameCount
    from
        (
            select
                t.teamId,
                g.day,
                t.refereeName,
                sum(g.count) as gameCount
            from
                team as t
                join game as g on
                    g.refereeName = t.refereeName
            group by
                1, 2, 3
        ) as g
        join refereeTeamCount as r on
            r.refereeName = g.refereeName
    group by
        1, 2;

-- select * from gamesByTeam order by gameCount desc;
-- select * from gamesByTeam order by teamId desc;

drop table if exists teamRefs;
create table teamRefs
(
    teamId      varchar(20),
    coachName   varchar(64),
    ref1        varchar(64) default '',
    ref2        varchar(64) default '',
    ref3        varchar(64) default '',
    ref4        varchar(64) default '',
    ref5        varchar(64) default '',
    ref6        varchar(64) default '',
    ref7        varchar(64) default '',
    ref8        varchar(64) default '',
    ref9        varchar(64) default '',
    ref10       varchar(64) default '',
    
    primary key (teamId, coachName)
);

insert ignore into teamRefs (teamId, coachName, ref1)
    select
        teamId,
        coachName,
        refereeName
    from
        team;

insert into teamRefs (teamId, coachName, ref2)
    select
        teamId,
        coachName,
        refereeName
    from
        team as t
    where
        not exists (
            select * from teamRefs as r where r.teamId = t.teamId and r.ref1 = t.refereeName
        )
on duplicate key
    update ref2 = t.refereeName;

insert into teamRefs (teamId, coachName, ref3)
    select
        teamId,
        coachName,
        refereeName
    from
        team as t
    where
        not exists (
            select * from teamRefs as r where r.teamId = t.teamId
                and (r.ref1 = t.refereeName
                or r.ref2 = t.refereeName)
        )
on duplicate key
    update ref3 = t.refereeName;

insert into teamRefs (teamId, coachName, ref4)
    select
        teamId,
        coachName,
        refereeName
    from
        team as t
    where
        not exists (
            select * from teamRefs as r where r.teamId = t.teamId
                and (r.ref1 = t.refereeName
                or r.ref2 = t.refereeName
                or r.ref3 = t.refereeName)
        )
on duplicate key
    update ref4 = t.refereeName;

insert into teamRefs (teamId, coachName, ref5)
    select
        teamId,
        coachName,
        refereeName
    from
        team as t
    where
        not exists (
            select * from teamRefs as r where r.teamId = t.teamId
                and (r.ref1 = t.refereeName
                or r.ref2 = t.refereeName
                or r.ref3 = t.refereeName
                or r.ref4 = t.refereeName)
        )
on duplicate key
    update ref5 = t.refereeName;

insert into teamRefs (teamId, coachName, ref6)
    select
        teamId,
        coachName,
        refereeName
    from
        team as t
    where
        not exists (
            select * from teamRefs as r where r.teamId = t.teamId
                and (r.ref1 = t.refereeName
                or r.ref2 = t.refereeName
                or r.ref3 = t.refereeName
                or r.ref4 = t.refereeName
                or r.ref5 = t.refereeName)
        )
on duplicate key
    update ref6 = t.refereeName;

insert into teamRefs (teamId, coachName, ref7)
    select
        teamId,
        coachName,
        refereeName
    from
        team as t
    where
        not exists (
            select * from teamRefs as r where r.teamId = t.teamId
                and (r.ref1 = t.refereeName
                or r.ref2 = t.refereeName
                or r.ref3 = t.refereeName
                or r.ref4 = t.refereeName
                or r.ref5 = t.refereeName
                or r.ref6 = t.refereeName)
        )
on duplicate key
    update ref7 = t.refereeName;

/* Games for Team */
select
    t.teamId,
    t.coachName,
    case when t.ref1 = '' then '' else concat(t.ref1, ": ", round(ifnull(r1.games,0) / ifnull(c1.teamCount, 1), 1)) end as 'ref1:games',
    case when t.ref2 = '' then '' else concat(t.ref2, ": ", round(ifnull(r2.games,0) / ifnull(c2.teamCount, 1), 1)) end as 'ref2:games',
    case when t.ref3 = '' then '' else concat(t.ref3, ": ", round(ifnull(r3.games,0) / ifnull(c3.teamCount, 1), 1)) end as 'ref3:games',
    case when t.ref4 = '' then '' else concat(t.ref4, ": ", round(ifnull(r4.games,0) / ifnull(c4.teamCount, 1), 1)) end as 'ref4:games',
    case when t.ref5 = '' then '' else concat(t.ref5, ": ", round(ifnull(r5.games,0) / ifnull(c5.teamCount, 1), 1)) end as 'ref5:games',
    case when t.ref6 = '' then '' else concat(t.ref6, ": ", round(ifnull(r6.games,0) / ifnull(c6.teamCount, 1), 1)) end as 'ref6:games',
    round(ifnull(r1.games,0) / ifnull(c1.teamCount, 1), 1)
        + round(ifnull(r2.games,0) / ifnull(c2.teamCount, 1), 1)
        + round(ifnull(r3.games,0) / ifnull(c3.teamCount, 1), 1)
        + round(ifnull(r4.games,0) / ifnull(c4.teamCount, 1), 1)
        + round(ifnull(r5.games,0) / ifnull(c5.teamCount, 1), 1)
        + round(ifnull(r6.games,0) / ifnull(c6.teamCount, 1), 1) as totalRefGames
from
    teamRefs as t
    left outer join refereeGameCount as r1 on
        r1.refereeName = t.ref1
    left outer join refereeTeamCount as c1 on
        c1.refereeName = t.ref1
    left outer join refereeGameCount as r2 on
        r2.refereeName = t.ref2
    left outer join refereeTeamCount as c2 on
        c2.refereeName = t.ref2
    left outer join refereeGameCount as r3 on
        r3.refereeName = t.ref3
    left outer join refereeTeamCount as c3 on
        c3.refereeName = t.ref3
    left outer join refereeGameCount as r4 on
        r4.refereeName = t.ref4
    left outer join refereeTeamCount as c4 on
        c4.refereeName = t.ref4
    left outer join refereeGameCount as r5 on
        r5.refereeName = t.ref5
    left outer join refereeTeamCount as c5 on
        c5.refereeName = t.ref5
    left outer join refereeGameCount as r6 on
        r6.refereeName = t.ref6
    left outer join refereeTeamCount as c6 on
        c6.refereeName = t.ref6
order by
    1;

/* Games By Day for Team */
select
    teamId,
    coachName,
    week1Total,
    week2Total,
    week3Total,
    week4Total,
    week5Total,
    week6Total,
    week7Total,
    week8Total,
    week1Total + week2Total + week3Total + week4Total + week5Total + week6Total + week7Total + week8Total as totalRefGames,
    week1VAP,
    week2VAP,
    week3VAP,
    week4VAP,
    week5VAP,
    week6VAP,
    week7VAP,
    week8VAP,
    week1VAP + week2VAP + week3VAP + week4VAP as firstHalfVAPRefGames,
    week5VAP + week6VAP + week7VAP + week8VAP as secondHalfVAPRefGames,
    week1VAP + week2VAP + week3VAP + week4VAP + week5VAP + week6VAP + week7VAP + week8VAP as totalVAPRefGames
from
(
    select
        t.teamId,
        t.coachName,
        round(ifnull(w1.gameCount, 0), 1) as week1Total,
        case when ifnull(w1.gameCount, 0) > 3 then 3 else round(ifnull(w1.gameCount, 0), 1) end as week1VAP,
        round(ifnull(w2.gameCount, 0), 1) as week2Total,
        case when ifnull(w2.gameCount, 0) > 6 then 6 else round(ifnull(w2.gameCount, 0), 1) end as week2VAP,
        round(ifnull(w3.gameCount, 0), 1) as week3Total,
        case when ifnull(w3.gameCount, 0) > 6 then 6 else round(ifnull(w3.gameCount, 0), 1) end as week3VAP,
        round(ifnull(w4.gameCount, 0), 1) as week4Total,
        case when ifnull(w4.gameCount, 0) > 3 then 3 else round(ifnull(w4.gameCount, 0), 1) end as week4VAP,
        round(ifnull(w5.gameCount, 0), 1) as week5Total,
        case when ifnull(w5.gameCount, 0) > 3 then 3 else round(ifnull(w5.gameCount, 0), 1) end as week5VAP,
        round(ifnull(w6.gameCount, 0), 1) as week6Total,
        case when ifnull(w6.gameCount, 0) > 6 then 6 else round(ifnull(w6.gameCount, 0), 1) end as week6VAP,
        round(ifnull(w7.gameCount, 0), 1) as week7Total,
        case when ifnull(w7.gameCount, 0) > 3 then 3 else round(ifnull(w7.gameCount, 0), 1) end as week7VAP,
        round(ifnull(w8.gameCount, 0), 1) as week8Total,
        case when ifnull(w8.gameCount, 0) > 3 then 3 else round(ifnull(w8.gameCount, 0), 1) end as week8VAP
    from
        (
            select teamId, coachName from team group by 1, 2
        ) as t
        left outer join gamesByTeam as w1 on
            w1.teamId = t.teamId
            and w1.gameDate = '2017-09-09'
        left outer join gamesByTeam as w2 on
            w2.teamId = t.teamId
            and w2.gameDate = '2017-09-16'
        left outer join gamesByTeam as w3 on
            w3.teamId = t.teamId
            and w3.gameDate = '2017-09-23'
        left outer join gamesByTeam as w4 on
            w4.teamId = t.teamId
            and w4.gameDate = '2017-09-30'
        left outer join gamesByTeam as w5 on
            w5.teamId = t.teamId
            and w5.gameDate = '2017-10-14'
        left outer join gamesByTeam as w6 on
            w6.teamId = t.teamId
            and w6.gameDate = '2017-10-21'
        left outer join gamesByTeam as w7 on
            w7.teamId = t.teamId
            and w7.gameDate = '2017-10-28'
        left outer join gamesByTeam as w8 on
            w8.teamId = t.teamId
            and w8.gameDate = '2017-11-04'
    ) as data
order by
    1;
