{% extends '/layout/master-admin.html' %}
{% block content %}

<!-- navbar -->
{% block navbar %} {% include '/admin/navbar.html' with {'username': username} %} {% endblock %}

<!-- tambah data barang -->
{% block data_barang %} {% include '/admin/data-barang.html' with {'products': products} %} {% endblock %}

<!-- footer -->
{% block footer %} {% include '/admin/footer.html' %} {% endblock %}

{% endblock %}
