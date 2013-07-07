<?php
class UserListStatus extends Enum
{
	const Dropped = 'D';
	const OnHold = 'H';
	const Completing = 'C';
	const Finished = 'F';
	const Planned = 'P';
	const Unknown = '?';
}
