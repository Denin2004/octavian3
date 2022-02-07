import React, {Component} from 'react';
import { Switch, Route, withRouter } from 'react-router-dom';

import { withCookies } from 'react-cookie';
import moment from 'moment-timezone';

import i18n from 'i18next';
import { initReactI18next } from 'react-i18next';

import {ConfigProvider, message} from 'antd';

import Login from '@app/web/Login';
import Main from '@app/web/Main';
import locales from '@app/web/locales';

i18n.use(initReactI18next) // passes i18n down to react-i18next
    .init({
        resources: locales.i18resources,
        lng: locales[locales.default].i18n,
        fallbackLng: locales[locales.default].i18n,
        interpolation: {
            escapeValue: false
        }
    });
    
moment.tz.setDefault('Etc/GMT0');
moment.locale(locales[locales.default].moment);


window.mfwApp.formats = {
    date: moment.localeData().longDateFormat('L'),
    time: moment.localeData().longDateFormat('LT'),
    datetime: moment.localeData().longDateFormat('L')+' '+moment.localeData().longDateFormat('LT'),
    datetimesec: moment.localeData().longDateFormat('L')+' '+moment.localeData().longDateFormat('LTS'),
    datetimesecmilli: moment.localeData().longDateFormat('L')+' '+moment.localeData().longDateFormat('LTS')+'.SSSSSS',
    datetimeToMoment: function(datetime) {return moment(datetime, window.mfwApp.formats.datetime)},
    dateToMoment: function(datetime) {return moment(datetime, window.mfwApp.formats.date)}
};    

class App extends Component {
    constructor(props){
        super(props);
        this.state = {
            locale: this.props.cookies.get('locale') ? this.props.cookies.get('locale') : locales.default
        };
    }

    componentDidMount() {

    }

    render() {
        return (
            <ConfigProvider locale={locales[this.state.locale].antd} componentSize="middle"> 
                <Switch>
                    <Route path="/login" component={Login} />
                    <Route path="*" component={Main} />
                </Switch>
            </ConfigProvider>
        )
    }
}

export default withRouter(withCookies(App));