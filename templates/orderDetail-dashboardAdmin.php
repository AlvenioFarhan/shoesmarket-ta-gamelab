{% extends '/layout/master-admin.html' %}
{% block content %}

<!-- navbar -->
{% block navbar %} {% include '/admin/navbar.html' with {'username': username} %} {% endblock %}

<!-- order detail -->
{% block order_detail %} {% include '/admin/order-detail.html' %} {% endblock %}

<!-- footer -->
{% block footer %} {% include '/admin/footer.html' %} {% endblock %}

{% endblock %}
