import React, {Component} from 'react';
import {generatePath} from 'react-router-dom';

import { Drawer, Button, Form, Space } from 'antd';
import { DownOutlined } from '@ant-design/icons';

import axios from 'axios';

import useWithForm from '@app/web/mfw/mfwForm/MfwFormHOC';
import { withTranslation } from 'react-i18next';
import QueryChoice from '@app/web/report/query/QueryChoice';
import LocationChoice from '@app/web/report/query/LocationChoice';
import CurrencyChoice from '@app/web/report/query/CurrencyChoice';
//import QueryRange from '@app/web/report/query/QueryRange';

class Query extends Component {
    constructor(props){
        super(props);
        this.state = {
            visible: false
        };
        this.getData = this.getData.bind(this);
    }

    getData() {
        this.setState({visible: false});
    }

    render() {
        console.log(this.props);
        return <React.Fragment>
            <Button  onClick={()=> this.setState({visible: true})}>{this.props.t(this.props.title)} <DownOutlined /></Button>
            { this.state.visible ?
            <Drawer visible={true}
              title={this.props.t(this.props.title)}
              closable={false}
              onClose={()=> this.setState({visible: false})}
              width={500}
              extra={<Space>
                  <Button onClick={()=> this.setState({visible: false})}>{this.props.t('common.cancel')}</Button>
                  <Button onClick={this.getData} type="primary">{this.props.t('common.show')}</Button>
                </Space>
               }>
                <Form form={this.props.form}
                   name="query"
                   layout="vertical">
                    {Object.keys(this.props.query).map(field => {
                        switch (this.props.query[field].type) {
                            case 'mfw-choice':
                                return <QueryChoice field={this.props.query[field]} key={field}/>
                            case 'mfw-location':
                                return <LocationChoice field={this.props.query[field]} key={field}/>
                            case 'mfw-currency':
                                return <CurrencyChoice field={this.props.query[field]} key={field}/>
/*                            case 'mfw-range':
                                return <QueryRange field={this.props.query[field]} key={field}/>                                */
                        }
                    })}
                </Form>
            </Drawer> : null}
        </React.Fragment>
    }
}

export default withTranslation()(useWithForm(Query));