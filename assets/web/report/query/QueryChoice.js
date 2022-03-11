import React, {Component} from 'react';

import { Form, Select } from 'antd';

import { withTranslation } from 'react-i18next';

class QueryChoice extends Component {
    
    render() {
        var choices = [...this.props.field.choices];
        if (this.props.field.translate === true) {
            choices[0].label = this.props.t(choices[0].label);
        }
        return (
            <Form.Item name={this.props.field.name}
                       label={this.props.t(this.props.field.label)}
                       initialValue={this.props.field.value == '' ? (choices.length != 0 ? choices[0].value : null) : this.props.field.value}>
                <Select options={this.props.field.choices}/>
            </Form.Item>
        )
    }
}

export default withTranslation()(QueryChoice);