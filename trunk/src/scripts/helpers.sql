select * from coach where name like "%Jesuele"; -- teamId 40
select * from coach where name like "%s Garcia"; -- teamId 46
select * from team where id in (40, 46);

--
-- Swap coaches for two teams
--
-- Second move
set @teamNameId1 = 'B12B5';
set @teamNameId2 = 'B12A6';

-- First move
set @teamNameId1 = 'B12A6';
set @teamNameId2 = 'B12A4';

select id into @teamId1 from team where nameId = @teamNameId1;
select id into @teamId2 from team where nameId = @teamNameId2;
select id into @coachId1 from coach where teamId = @teamId1;
select id into @coachId2 from coach where teamId = @teamId2;

-- select @teamId1, @coachId1, @teamId2, @coachId2;

update coach set teamId = 99999 where id = @coachId1;
update coach set teamId = @teamId1 where id = @coachId2;
update coach set teamId = @teamId2 where id = @coachId1;

select name, region, city into @name1, @region1, @city1 from team where id = @teamId1;
select name, region, city into @name2, @region2, @city2 from team where id = @teamId2;

update team set
    name = @name2,
    region = @region2,
    city = @city2
where
    id = @teamId1;

update team set
    name = @name1,
    region = @region1,
    city = @city1
where
    id = @teamId2;

-- Set home team in game to a different team
set @teamId = 72;
set @gameId = 108;
update game set homeTeamId = @teamId where id = @gameId;

--
-- Swap homeTeamId and visitingTeamId for game
--
set @gameId = 652;
select
    homeTeamId,
    visitingTeamId
into
    @homeTeamId,
    @visitingTeamId
from
    game
where
    id = @gameId;

update
    game
set
    homeTeamId = @visitingTeamId,
    visitingTeamId = @homeTeamId
where
    id = @gameId;

-- Set home team in game to a different team
set @teamId = 74;
set @gameId = 109;
update game set homeTeamId = @teamId where id = @gameId;

-- Set visiting team in game to a different team
set @teamId = 69;
set @gameId = 99;
update game set visitingTeamId = @teamId where id = @gameId;

-- Set home team in game to a different team
set @teamId = 67;
set @gameId = 98;
update game set homeTeamId = @teamId where id = @gameId;

--
-- Swap homeTeamId and visitingTeamId for game
--
set @gameId = 99;
select
    homeTeamId,
    visitingTeamId
into
    @homeTeamId,
    @visitingTeamId
from
    game
where
    id = @gameId;

update
    game
set
    homeTeamId = @visitingTeamId,
    visitingTeamId = @homeTeamId
where
    id = @gameId;
    
--
-- Replace team with a new team
--
select id, teamId into @coachId, @teamId from coach where name = 'Robert Jesuele';

update coach
set
    name = 'Coach Prieto'
where
    id = @coachId;

update team
set
    name    = 'La Esperanza 2006',
    region  = '',
    city    = 'Oxnard'
where
    id = @teamId;
