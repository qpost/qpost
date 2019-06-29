<?php

namespace qpost\Account;

class ProfileViewStatus {
	public const OK = 0;
	public const BLOCKED = 1;
	public const EMAIL_NOT_ACTIVATED = 2;
	public const CLOSED = 3;
	public const PRIVATE = 4;
	public const SUSPENDED = 5;
}