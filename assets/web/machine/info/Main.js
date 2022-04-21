import React, {Component} from 'react';
import {generatePath} from 'react-router-dom';

import { Tabs, message } from 'antd';

import axios from 'axios';
import { withTranslation } from 'react-i18next';

import Status from '@app/web/machine/info/Status';

class MachineInfo extends Component {
    constructor(props){
        super(props);
        this.state = {
        };
    }

    render() {
        return (<Tabs defaultActiveKey="1">
            <Tabs.TabPane tab={this.props.t('common.main_info')} key="1">
                <Tabs defaultActiveKey="1">
                    <Tabs.TabPane tab={this.props.t('machine.status.m_status')} key="1">
                        <Status info={this.props.info}/>
                    </Tabs.TabPane>
                    <Tabs.TabPane tab={this.props.t('machine.advanced_parameters')} key="2">
                    </Tabs.TabPane>
                    <Tabs.TabPane tab={this.props.t('event.last_24')} key="3">
                    </Tabs.TabPane>
                    <Tabs.TabPane tab={this.props.t('machine.service_requests.self_name')} key="4">
                    </Tabs.TabPane>
                </Tabs>
            </Tabs.TabPane>   
            <Tabs.TabPane tab={this.props.t('common.configuration')} key="2">
            </Tabs.TabPane>
            <Tabs.TabPane tab={this.props.t('game.games')} key="3">
                <Tabs defaultActiveKey="1">
                    <Tabs.TabPane tab={this.props.t('common.summary')} key="1">
                    </Tabs.TabPane>
                    <Tabs.TabPane tab={this.props.t('game.last_played')} key="2">
                    </Tabs.TabPane>
                    <Tabs.TabPane tab={this.props.t('common.configuration')} key="3">
                    </Tabs.TabPane>
                </Tabs>
            </Tabs.TabPane>
            <Tabs.TabPane tab={this.props.t('player.players')} key="4">
                <Tabs defaultActiveKey="1">
                    <Tabs.TabPane tab={this.props.t('player.last')} key="1">
                    </Tabs.TabPane>
                    <Tabs.TabPane tab={this.props.t('session.last')} key="2">
                    </Tabs.TabPane>
                    <Tabs.TabPane tab={this.props.t('player.top')} key="3">
                    </Tabs.TabPane>
                </Tabs>
            </Tabs.TabPane>
            <Tabs.TabPane tab={this.props.t('machine.meter.meters')} key="5">
            </Tabs.TabPane>
            <Tabs.TabPane tab={this.props.t('findata.finance')} key="6">
            </Tabs.TabPane>
            <Tabs.TabPane tab={this.props.t('common.transactions')} key="7">
                <Tabs defaultActiveKey="1">
                    <Tabs.TabPane tab={this.props.t('ticket.tickets')} key="1">
                    </Tabs.TabPane>
                    <Tabs.TabPane tab={this.props.t('findata.cashless.cashless')} key="2">
                    </Tabs.TabPane>
                    <Tabs.TabPane tab={this.props.t('cashdesk.payouts._payouts')} key="3">
                    </Tabs.TabPane>
                    <Tabs.TabPane tab={this.props.t('prize.transferred')} key="4">
                    </Tabs.TabPane>
                </Tabs>
            </Tabs.TabPane>
            <Tabs.TabPane tab={this.props.t('jackpot.jackpots')} key="8">
                <Tabs defaultActiveKey="1">
                    <Tabs.TabPane tab={this.props.t('common.assigned')} key="1">
                    </Tabs.TabPane>
                    <Tabs.TabPane tab={this.props.t('jackpot.last_paid')} key="2">
                    </Tabs.TabPane>
                </Tabs>
            </Tabs.TabPane>
        </Tabs>);
    }
}

export default withTranslation()(MachineInfo);