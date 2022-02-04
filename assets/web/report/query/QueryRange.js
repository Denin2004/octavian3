import React, {Component} from 'react';

import { Form, DatePicker } from 'antd';

import { withTranslation } from 'react-i18next';
import moment from 'moment-timezone';

class QueryRange extends Component {
    
    static ranges = {
        startMonth: {
            title: 'date_block.start_month',
            value: [
                moment().startOf('month'),
                moment().endOf('day')
            ]
        },
        prevMonth: {
            title: 'date_block.prev_month',
            value: [
                moment().subtract(1, 'month').startOf('month'),
                moment().subtract(1, 'month').endOf('month')
            ]
        },
        lastDay: {
            title: 'date_block.last_day',
            value: [
                moment().subtract(1, 'day').startOf('day'),
                moment().subtract(1, 'day').endOf('day')
            ]
        },
        last2Day: {
            title: 'date_block.2_day',
            value:[
                moment().subtract(1, 'day').startOf('day'),
                moment().endOf('day')                
            ]
        },
        last3Day: {
            title: 'date_block.3_day',
            value:[
                moment().subtract(2, 'day').startOf('day'),
                moment().endOf('day')
            ]
        },
        prevWeek: {
            title: 'date_block.prev_week',
            value:[
                moment().subtract(1, 'week').weekday(0).startOf('week'),
                moment().subtract(1, 'week').weekday(0).endOf('week')                
            ]
        },
        lastGamingDay: {
            title: 'date_block.last_gaming_day',
            value:[
                moment().subtract(1, 'day').startOf('day'),
                moment().endOf('day')
            ]
        },
        last2GamingDay: {
            title: 'date_block.2_gaming_day',
            value:[
                moment().subtract(2, 'day').startOf('day'),
                moment().endOf('day')
            ]
        },
        last3GamingDay: {
            title: 'date_block.3_gaming_day',
            value:[
                moment().subtract(3, 'day').startOf('day'),
                moment().endOf('day')
            ]
        },
        today: {
            title: 'date_block.today',
            value:[
                moment().startOf('day'),
                moment().endOf('day')
            ]
        },
        lastHour: {
            title: 'date_block.last_hour',
            value:[
                moment().subtract(1, 'hours'),
                moment()
            ]
        },
        last2Hour: {
            title: 'date_block.last_2_hour',
            value:[
                moment().subtract(2, 'hours'),
                moment()                
            ]
        },
        last4Hour: {
            title: 'date_block.last_4_hour',
            value:[
                moment().subtract(4, 'hours'),
                moment()
            ]
        },
        last12Hour: {
            title: 'date_block.last_12_hour',
            value:[
                moment().subtract(12, 'hours'),
                moment()
            ]
        },
        last24Hour: {
            title: 'date_block.last_24_hour',
            value:[
                moment().subtract(24, 'hours'),
                moment()
            ]
        },
        last48Hour: {
            title: 'date_block.last_48_hour',
            value:[
                moment().subtract(48, 'hours'),
                moment()
            ]
        },
        last7Days: {
            title: 'date_block.7_day',
            value:[
                moment().subtract(6, 'day').startOf('day'),
                moment().endOf('day')
            ]
        }
    };
    
    constructor(props){
        super(props);
        var r = {};
        if (this.props.field.options.custom.periods != undefined) {
            this.props.field.options.custom.periods.map(opt => {
                if (opt != 'custom') {
                    r[this.props.t(QueryRange.ranges[opt].title)] = QueryRange.ranges[opt].value;
                }
            });
        }
        this.state = {
            format: this.props.field.options.addTime ? 
                window.mfwApp.formats.datetime : window.mfwApp.formats.date,
                ranges: r,
                initValue: this.props.field.options.custom.value ? QueryRange.ranges[this.props.field.options.custom.value].value : []
        };
    }
    
    render() {
        return (
            <Form.Item name={this.props.field.name}
                label={this.props.t(this.props.field.label)}
                initialValue={this.state.initValue}>
                <DatePicker.RangePicker 
                  showTime={this.props.field.options.addTime}
                  format={this.state.format}
                  ranges={this.state.ranges}/>
            </Form.Item>
        )
    }
}

export default withTranslation()(QueryRange);