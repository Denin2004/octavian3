import React, {Component} from 'react';

import { Form, Select } from 'antd';

import { withTranslation } from 'react-i18next';

class QueryChoice extends Component {
    
    render() {
        return (
            <Form.Item name={this.props.field.name}
                       label={this.props.t(this.props.field.label)}
                       initialValue={this.props.field.value}>
                <Select  options={this.props.field.choices}/>
            </Form.Item>
        )
    }
}

export default withTranslation()(QueryChoice);