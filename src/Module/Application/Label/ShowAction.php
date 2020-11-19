<?php
/*
 * vim:set softtabstop=4 shiftwidth=4 expandtab:
 *
 *  LICENSE: GNU Affero General Public License, version 3 (AGPL-3.0-or-later)
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

namespace Ampache\Module\Application\Label;

use Ampache\Config\AmpConfig;
use Ampache\Config\ConfigContainerInterface;
use Ampache\Model\Label;
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

    private ConfigContainerInterface $configContainer;

    private UiInterface $ui;

    public function __construct(
        ConfigContainerInterface $configContainer,
        UiInterface $ui
    ) {
        $this->configContainer = $configContainer;
        $this->ui              = $ui;
    }

    public function run(ServerRequestInterface $request, GuiGatekeeperInterface $gatekeeper): ?ResponseInterface
    {
        $this->ui->showHeader();
        
        $label_id = (int) filter_input(INPUT_GET, 'label', FILTER_SANITIZE_NUMBER_INT);
        if (!$label_id) {
            if (!empty($_REQUEST['name'])) {
                $label_id = Label::lookup($_REQUEST);
            }
        }
        if ($label_id > 0) {
            $label = new Label($label_id);
            $label->format();
            $object_ids  = $label->get_artists();
            $object_type = 'artist';
            require_once Ui::find_template('show_label.inc.php');
            
            $this->ui->showFooter();

            return null;
        }
        if (
            Access::check('interface', 50) ||
            AmpConfig::get('upload_allow_edit')
        ) {
            require_once Ui::find_template('show_add_label.inc.php');
        } else {
            echo T_('The Label cannot be found');
        }
        
        $this->ui->showQueryStats();
        $this->ui->showFooter();
        
        return null;
    }
}
