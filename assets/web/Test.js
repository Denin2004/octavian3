import React, {Component} from 'react';
import { withTranslation } from 'react-i18next';

import ResizeObserver from 'rc-resize-observer';
import axios from 'axios';

import MfwTable from '@app/web/mfw/mfwTable/mfwMain';

var columns = [
    {
        "title": "machine._machine",
        dataIndex: "MACH_ID",
        "type": "mfw-int,mfw-link",
        "numSort": true,
        "action": {
            "route": "machineInfo",
            "ajax": true,
            "params": {
                "id": "row:MACH_ID",
                "modal": "val:1"
            },
            "attrs": {
                "data-mfw_method": "get"
            }
        },
        "mfw_filter": "combo",
        "width": 200
    },
    {
        "title": "machine.info.position",
        dataIndex: "POSITION",
        "mfw_filter": "combo",
        "type": "mfw-int"
    },
    {
        "title": "event.date_server",
        dataIndex: "EXCEP_TIMESTAMP",
        "type": "mfw-date-time-milli",
        "mfw_nogroup": true
    },
    {
        "title": "event.description",
        dataIndex: "CODE_DESC",
        "mfw_filter": "combo",
        width: 200
    },
    {
        "title": "machine.info.software_set",
        dataIndex: "SOFTWARE",
        "mfw_filter": "combo",
        width: 100
    },    
    {
        "title": "player.name",
        dataIndex: "PLAYER_NAME",
        "type": "mfw-link",
        "mfw_filter": "combo"
    },
    {
        "title": "player.card._card",
        dataIndex: "PLAYER_CARD",
        "type": "mfw-link",
        "mfw_filter": "combo"
    },
    {
        "title": "event_analysis.money.cta",
        dataIndex: "CURR_TRX_AMNT_MONEY",
        "type": "mfw-num",
        "mfw_nogroup": true,
        width: 100
    },
    {
        "title": "event_analysis.cta_credits",
        dataIndex: "CURRENT_TRUNS_AMOUNT",
        "type": "mfw-int",
        "mfw_nogroup": true,
        width: 100
    },
    {
        "title": "event_analysis.money.credit",
        dataIndex: "CRD_COST",
        "type": "mfw-num",
        "mfw_total_group": "sum",        
        "mfw_filter": "combo",
        width: 100
    },
    {
        "title": "event_analysis.credits.total_drop",
        dataIndex: "TOTAL_DROP",
        "type": "mfw-int",
        "mfw_nogroup": true,
        width: 100
    },
    {
        "title": "event_analysis.credits.total_cancelled",
        dataIndex: "NON_DEDUCTIBLE",
        "type": "mfw-int",
        "mfw_nogroup": true,
        width: 100
    },
    {
        "title": "event_analysis.credits.handpaid_jp",
        dataIndex: "HANDPAY",
        "type": "mfw-int",
        "mfw_nogroup": true,
        width: 100
    },
    {
        "title": "event_analysis.credits.current_credit",
        dataIndex: "CURRENT_CREDIT",
        "type": "mfw-int",
        "mfw_nogroup": true,
        width: 100
    },
    {
        "title": "event_analysis.credits.total_bet",
        dataIndex: "TOTAL_IN",
        "type": "mfw-int",
        "mfw_nogroup": true,
        width: 100
    },
    {
        "title": "event_analysis.credits.total_win",
        dataIndex: "TOTAL_OUT",
        "type": "mfw-int",
        "mfw_nogroup": true,
        width: 100
    },
    {
        "title": "event_analysis.units.games",
        dataIndex: "GAMES",
        "type": "mfw-int",
        "mfw_nogroup": true,
        width: 100
    },
    {
        "title": "event_analysis.credits.paid_jp",
        dataIndex: "DEDUCTIBLE",
        "type": "mfw-int",
        "mfw_nogroup": true,
        width: 100
    },
    {
        "title": "event_analysis.credits.hp_cancelled",
        dataIndex: "CANCEL_CREDIT",
        "type": "mfw-int",
        "mfw_nogroup": true,
        width: 100
    },
    {
        "title": "event_analysis.credits.coins_to_drop",
        dataIndex: "TOTAL_COINS_TO_DROP",
        "type": "mfw-int",
        "mfw_nogroup": true,
        width: 100
    },
    {
        "title": "event_analysis.credits.coins_in",
        dataIndex: "TRUE_COIN_IN",
        "type": "mfw-int",
        "mfw_nogroup": true,
        width: 100
    },
    {
        "title": "event_analysis.credits.coins_out",
        dataIndex: "TRUE_COIN_OUT",
        "type": "mfw-int",
        "mfw_nogroup": true,
        width: 100
    },
    {
        "title": "event_analysis.credits.bills_in",
        dataIndex: "TOTAL_BILLS",
        "type": "mfw-int",
        "mfw_nogroup": true,
        width: 100
    },
    {
        "title": "event_analysis.credits.bills_out",
        dataIndex: "MATCHED_WAGERS",
        "type": "mfw-int",
        "mfw_nogroup": true,
        width: 100
    },
    {
        "title": "event_analysis.credits.cashless_in",
        dataIndex: "DOWNLOADED_CASHABLE",
        "type": "mfw-int",
        "mfw_nogroup": true,
        width: 100
    },
    {
        "title": "event_analysis.credits.cashless_out",
        dataIndex: "CREDITS_UPLOADED",
        "type": "mfw-int",
        "mfw_nogroup": true,
        width: 100
    },
    {
        "title": "event_analysis.credits.promo_cashless_in",
        dataIndex: "DOWNLOADED_PROMOTIONAL",
        "type": "mfw-int",
        "mfw_nogroup": true,
        width: 100
    },
    {
        "title": "event_analysis.credits.promo_cashless_out",
        dataIndex: "DOWNLOADED_NON_CASHABLE",
        "type": "mfw-int",
        "mfw_nogroup": true,
        width: 100
    },
    {
        "title": "event_analysis.cents.tickets_in",
        dataIndex: "TICKET_IN",
        "type": "mfw-int",
        "mfw_nogroup": true,
        width: 100
    },
    {
        "title": "event_analysis.cents.promo_tickets_in",
        dataIndex: "RESTRICTED_TICKET_IN",
        "type": "mfw-int",
        "mfw_nogroup": true,
        width: 100
    },
    {
        "title": "event_analysis.cents.tickets_out",
        dataIndex: "TICKET_OUT",
        "type": "mfw-int",
        "mfw_nogroup": true,
        width: 100
    },
    {
        "title": "event_analysis.cents.promo_tickets_out",
        dataIndex: "RESTRICTED_TICKET_OUT",
        "type": "mfw-int",
        "mfw_nogroup": true,
        width: 100
    },
    {
        "title": "event_analysis.credits.paytable_win",
        dataIndex: "PAYTABLE_WIN",
        "type": "mfw-int",
        "mfw_nogroup": true,
        width: 100
    },
    {
        "title": "event_analysis.credits.paytable_hp_jp",
        dataIndex: "PAYTABLE_HP_JACKPOT",
        "type": "mfw-int",
        "mfw_nogroup": true,
        width: 100
    },
    {
        "title": "event_analysis.credits.progressive_win",
        dataIndex: "PROGRESSIVE_WIN",
        "type": "mfw-int",
        "mfw_nogroup": true,
        width: 100
    },
    {
        "title": "event_analysis.credits.progressive_hp_jp",
        dataIndex: "PROGRESSIVE_HP_JACKPOT",
        "type": "mfw-int",
        "mfw_nogroup": true,
        width: 100
    },
    {
        "title": "event_analysis.credits.mech_total_bet",
        dataIndex: "MECH_TOTAL_IN",
        "type": "mfw-int",
        "mfw_nogroup": true,
        width: 100
    },
    {
        "title": "event_analysis.credits.mech_total_win",
        dataIndex: "MECH_TOTAL_OUT",
        "type": "mfw-int",
        "mfw_nogroup": true,
        width: 100
    },
    {
        "title": "event_analysis.credits.mech_total_in",
        dataIndex: "GAMES_WON",
        "type": "mfw-int",
        "mfw_nogroup": true,
        width: 100
    },
    {
        "title": "event_analysis.credits.mech_total_out",
        dataIndex: "MAX_WIN",
        "type": "mfw-int",
        "mfw_nogroup": true,
        width: 100
    },
    {
        "title": "event_analysis.units.bill.in.5",
        dataIndex: "BILL_2_IN",
        "type": "mfw-int",
        "mfw_nogroup": true,
        width: 100
    },
    {
        "title": "event_analysis.units.bill.in.10",
        dataIndex: "BILL_3_IN",
        "type": "mfw-int",
        "mfw_nogroup": true,
        width: 100
    },
    {
        "title": "event_analysis.units.bill.in.20",
        dataIndex: "BILL_4_IN",
        "type": "mfw-int",
        "mfw_nogroup": true,
        width: 100
    },
    {
        "title": "event_analysis.units.bill.in.25",
        dataIndex: "BILL_5_IN",
        "type": "mfw-int",
        "mfw_nogroup": true,
        width: 100
    },
    {
        "title": "event_analysis.units.bill.in.50",
        dataIndex: "BILL_6_IN",
        "type": "mfw-int",
        "mfw_nogroup": true,
        width: 100
    },
    {
        "title": "event_analysis.units.bill.in.100",
        dataIndex: "BILL_7_IN",
        "type": "mfw-int",
        "mfw_nogroup": true,
        width: 100
    },
    {
        "title": "event_analysis.units.bill.in.200",
        dataIndex: "BILL_8_IN",
        "type": "mfw-int",
        "mfw_nogroup": true,
        width: 100
    },
    {
        "title": "event_analysis.units.bill.in.250",
        dataIndex: "BILL_9_IN",
        "type": "mfw-int",
        "mfw_nogroup": true,
        width: 100
    },
    {
        "title": "event_analysis.units.bill.in.500",
        dataIndex: "BILL_10_IN",
        "type": "mfw-int",
        "mfw_nogroup": true,
        width: 100
    },
    {
        "title": "event_analysis.units.bill.in.1000",
        dataIndex: "BILL_11_IN",
        "type": "mfw-int",
        "mfw_nogroup": true,
        width: 100
    },
    {
        "title": "event_analysis.units.bill.in.5000",
        dataIndex: "BILL_14_IN",
        "type": "mfw-int",
        "mfw_nogroup": true,
        width: 100
    },
    {
        "title": "event_analysis.units.bill.out.5",
        dataIndex: "BILL_2_OUT",
        "type": "mfw-int",
        "mfw_nogroup": true,
        width: 100
    },
    {
        "title": "event_analysis.units.bill.out.10",
        dataIndex: "BILL_3_OUT",
        "type": "mfw-int",
        "mfw_nogroup": true,
        width: 100
    },
    {
        "title": "event_analysis.units.bill.out.20",
        dataIndex: "BILL_4_OUT",
        "type": "mfw-int",
        "mfw_nogroup": true,
        width: 100
    },
    {
        "title": "event_analysis.units.bill.out.25",
        dataIndex: "BILL_5_OUT",
        "type": "mfw-int",
        "mfw_nogroup": true,
        width: 100
    },
    {
        "title": "event_analysis.units.bill.out.50",
        dataIndex: "BILL_6_OUT",
        "type": "mfw-int",
        "mfw_nogroup": true,
        width: 100
    },
    {
        "title": "event_analysis.units.bill.out.100",
        dataIndex: "BILL_7_OUT",
        "type": "mfw-int",
        "mfw_nogroup": true,
        width: 100
    },
    {
        "title": "event_analysis.units.bill.out.200",
        dataIndex: "BILL_8_OUT",
        "type": "mfw-int",
        "mfw_nogroup": true,
        width: 100
    },
    {
        "title": "event_analysis.units.bill.out.250",
        dataIndex: "BILL_9_OUT",
        "type": "mfw-int",
        "mfw_nogroup": true,
        width: 100
    },
    {
        "title": "event_analysis.units.bill.out.500",
        dataIndex: "BILL_10_OUT",
        "type": "mfw-int",
        "mfw_nogroup": true,
        width: 100
    },
    {
        "title": "event_analysis.units.bill.out.1000",
        dataIndex: "BILL_11_OUT",
        "type": "mfw-int",
        "mfw_nogroup": true,
        width: 100
    },
    {
        "title": "event_analysis.units.bill.out.5000",
        dataIndex: "BILL_14_OUT",
        "type": "mfw-int",
        "mfw_nogroup": true,
        width: 100
    },
    {
        "title": "event_analysis.units.ticket_in_count",
        dataIndex: "TICKET_IN_CNT",
        "type": "mfw-int",
        "mfw_nogroup": true,
        width: 100
    },
    {
        "title": "event_analysis.units.promo_ticket_in_count",
        dataIndex: "TICKET_PROMO_IN_CNT",
        "type": "mfw-int",
        "mfw_nogroup": true,
        width: 100
    },
    {
        "title": "event_analysis.units.ticket_out_count",
        dataIndex: "TICKET_OUT_CNT",
        "type": "mfw-int",
        "mfw_nogroup": true,
        width: 100
    },
    {
        "title": "event_analysis.units.promo_ticket_out_count",
        dataIndex: "TICKET_PROMO_OUT_CNT",
        "type": "mfw-int",
        "mfw_nogroup": true,
        width: 100
    },
    {
        "title": "location._location",
        dataIndex: "AREA",
        "type": "mfw-link",
        "mfw_filter": "combo",
        width: 150
    },
    {
        "title": "machine.info.vendor",
        dataIndex: "VENDOR",
        "mfw_filter": "combo",
        width: 100
    },
    {
        "title": "machine.info.cabinet",
        dataIndex: "CABINET",
        "mfw_filter": "combo",
        width: 100
    },
    {
        "title": "machine.info.protocol",
        dataIndex: "PROTOCOL_NAME",
        "mfw_filter": "combo",
        width: 100
    },
    {
        "title": "machine.equipment.code",
        dataIndex: "ADD_EQU",
        "type": "mfw-int",
        "mfw_nogroup": true,
        width: 100
    },
    {
        "title": "event_analysis.ext_info_code",
        dataIndex: "CURRENT_BILLS",
        "type": "mfw-int",
        "mfw_nogroup": true,
        width: 100
    },
    {
        "title": "glm.message_counter",
        dataIndex: "GLM_MESSAGE_COUNTER",
        "type": "mfw-int",
        "mfw_nogroup": true,
        width: 100
    },
    {
        "title": "event.date_ucb",
        dataIndex: "SYSTEM_TIME",
        "type": "custom,mfw-date-time",
        "mfw_nogroup": true,
        width: 100
    },
    {
        "title": "event.code",
        dataIndex: "EVENT_CODE",
        "mfw_nogroup": true,
        width: 100
    },
    {
        "title": "player.card.p_type",
        dataIndex: "PLAYER_CARD_TYPE",
        "mfw_nogroup": true,
        width: 100
    },
    {
        "title": "findata.bills.inserted",
        dataIndex: "BILLS_INSERTED",
        "type": "mfw-int",
        "mfw_nogroup": true,
        width: 100
    }
];    

var data = [];

class Test extends Component {
    constructor(props){
        super(props);
        this.test = this.test.bind(this);
        this.state = {loading: true}
    }
    
    test(params) {
        //console.log(params);
    }

    componentDidMount() {
        axios.get(
            '/data.json',
            {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            }
        ).then(res => {
            /*res.data[0]['key'] = 1;
            res.data[1]['key'] = 2;
            res.data[2]['key'] = 3;
            data = [res.data[0], res.data[1], res.data[2]];*/
            data = res.data;
            
            this.setState({
                loading: false
            });
        }).catch(error => {
            if (error.response) {
                console.log(error);
            } else {
                console.log(error);
            }
        });
    }

    render() {
/*        return (
            <MfwTable
                columns={columns}
                dataSource={data}
                scroll={{
                  y: 300,
                  x: '100vw',
                }}
              />                
        );
        
        return (
                <div className="flex">
                    <div className="header">Верхний блок</div>
                    <ResizeObserver onResize={this.test}>
                        <div className="h100">wwwww</div>
                    </ResizeObserver>
                    <div className="footer">Нижний блок</div>
                </div>
        )*/
        if (this.state.loading == true) {
            return <div>loading</div>;
        }
        
        //colGroup={['MACH_ID', 'CODE_DESC', 'SOFTWARE']}
        
        return (
                <div className="flex">
                    <div className="header">Верхний блок</div>
                        <MfwTable
                            mfwColumns={columns}
                            mfwData={data}
                            multiSelect={true}
                            rowKey="EXCEP_TIMESTAMP"
                            colGroup={['MACH_ID', 'CODE_DESC', 'SOFTWARE']}
                            scroll={{
                                y: 300
                            }}
                        />
                    <div className="footer">Нижний блок</div>
                </div>
        )
        
/*        return (
        <React.Fragment>Main web</React.Fragment>
        )*/
    }
}

export default withTranslation()(Test);