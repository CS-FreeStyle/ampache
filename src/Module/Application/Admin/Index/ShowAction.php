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

declare(strict_types=0);

namespace Ampache\Module\Application\Admin\Index;

use Ampache\Model\Catalog;
use Ampache\Model\ModelFactoryInterface;
use Ampache\Module\Application\ApplicationActionInterface;
use Ampache\Module\Authorization\GuiGatekeeperInterface;
use Ampache\Module\Authorization\Access;
use Ampache\Module\Util\Ui;
use Ampache\Module\Util\UiInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class ShowAction implements ApplicationActionInterface
{
    public const REQUEST_KEY = 'show';

    private UiInterface $ui;

    private ModelFactoryInterface $modelFactory;

    public function __construct(
        UiInterface $ui,
        ModelFactoryInterface $modelFactory
    ) {
        $this->ui           = $ui;
        $this->modelFactory = $modelFactory;
    }

    public function run(ServerRequestInterface $request, GuiGatekeeperInterface $gatekeeper): ?ResponseInterface
    {
        if (!Access::check('interface', 75)) {
            Ui::access_denied();

            return null;
        }

        $this->ui->showHeader();

        // Show Catalogs
        $catalog_ids = Catalog::get_catalogs();

        $browse = $this->modelFactory->createBrowse();
        $browse->set_type('catalog');
        $browse->set_static_content(true);
        $browse->save_objects($catalog_ids);
        $browse->show_objects($catalog_ids);
        $browse->store();

        $this->ui->showQueryStats();
        $this->ui->showFooter();

        return null;
    }
}
