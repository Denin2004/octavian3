import React, {Component} from 'react';
import {generatePath} from 'react-router-dom';

import { Drawer, Button, Form, Space, Typography } from 'antd';
import { DownOutlined } from '@ant-design/icons';

import axios from 'axios';

import useWithForm from '@app/web/mfw/mfwForm/MfwFormHOC';
import { withTranslation } from 'react-i18next';
import QueryChoice from '@app/web/report/query/QueryChoice';
import LocationChoice from '@app/web/report/query/LocationChoice';
import CurrencyChoice from '@app/web/report/query/CurrencyChoice';
import QueryRange from '@app/web/report/query/QueryRange';

class Query extends Component {
    constructor(props){
        super(props);
        this.state = {
            visible: false
        };
        this.show = this.show.bind(this);
    }

    show(values) {
        this.setState({visible: false});
        this.props.success(values);
    }

    render() {
        return <React.Fragment>
            <Button type="link" onClick={()=> this.setState({visible: true})}>{this.props.t(this.props.title)} <DownOutlined /></Button>
            <Typography.Text className="mfw-query-text">{this.props.queryText}</Typography.Text>
            <Drawer visible={this.state.visible}
              title={this.props.t(this.props.title)}
              closable={false}
              onClose={()=> this.setState({visible: false})}
              width={500}
              extra={<Space>
                  <Button onClick={()=> this.setState({visible: false})}>{this.props.t('common.cancel')}</Button>
                  <Button onClick={() => this.props.form.submit()} type="primary">{this.props.t('common.show')}</Button>
                </Space>
               }>
                <Form form={this.props.form}
                   name="query"
                   layout="vertical"
                   onFinish={this.show}>
                    {Object.keys(this.props.query).map(field => {
                        switch (this.props.query[field].type) {
                            case 'mfw-choice':
                                return <QueryChoice field={this.props.query[field]} key={field}/>
                            case 'mfw-location':
                                return <LocationChoice field={this.props.query[field]} key={field}/>
                            case 'mfw-currency':
                                return <CurrencyChoice field={this.props.query[field]} key={field}/>
                            case 'mfw-range':
                                return <QueryRange field={this.props.query[field]} key={field}/>
                        }
                    })}
                </Form>
            </Drawer>
        </React.Fragment>
    }
}

export default withTranslation()(useWithForm(Query));