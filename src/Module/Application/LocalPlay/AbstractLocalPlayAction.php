<?php
/*
 * vim:set softtabstop=4 shiftwidth=4 expandtab:
 *
 * LICENSE: GNU Affero General Public License, version 3 (AGPL-3.0-or-later)
 * Copyright 2001 - 2020 Ampache.org
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 */

namespace Ampache\Module\Application\LocalPlay;

use Ampache\Config\ConfigContainerInterface;
use Ampache\Config\ConfigurationKeyEnum;
use Ampache\Module\Application\ApplicationActionInterface;
use Ampache\Module\Authorization\GuiGatekeeperInterface;
use Ampache\Module\Authorization\Access;
use Ampache\Module\Util\Ui;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

abstract class AbstractLocalPlayAction implements ApplicationActionInterface
{
    private ConfigContainerInterface $configContainer;

    protected function __construct(
        ConfigContainerInterface $configContainer
    ) {
        $this->configContainer = $configContainer;
    }

    public function run(ServerRequestInterface $request, GuiGatekeeperInterface $gatekeeper): ?ResponseInterface
    {
        if (
            $this->configContainer->isFeatureEnabled(ConfigurationKeyEnum::ALLOW_LOCALPLAY_PLAYBACK) === false ||
            !Access::check('interface', 25)
        ) {
            Ui::access_denied();

            return null;
        }

        return $this->handle($request);
    }

    protected function showRefresh(): void
    {
        $refresh_limit = $this->configContainer->get(ConfigurationKeyEnum::REFRESH_LIMIT) ?? 0;
        if ($refresh_limit > 5) {
            $ajax_url      = '?page=localplay&action=command&command=refresh';
            require_once Ui::find_template('javascript_refresh.inc.php');
        }
    }

    abstract protected function handle(ServerRequestInterface $request): ?ResponseInterface;
}
