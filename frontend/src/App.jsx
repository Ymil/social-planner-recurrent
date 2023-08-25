import { useEffect, useState } from "react";
import "./App.scss";

function App() {
  const [posts, setPosts] = useState([]);
  const [configDatetime, setConfigDatetime] = useState({
    date: "monday",
    time: "12:00",
  });

  useEffect(() => {
    if (typeof SC_PRE_DATA_POSTS !== "undefined") {
      setPosts(SC_PRE_DATA_POSTS);
      setConfigDatetime({ time: SC_PRE_DATA_TIME, date: SC_PRE_DATA_DATE });
    }
  }, []);

  useEffect(() => {
    const checkboxes = document.querySelectorAll('input[type="checkbox"]');

    checkboxes.forEach((checkbox) => {
      const provider = checkbox.value;
      checkbox.checked = SC_PRE_DATA_TASKS_PROVIDERS.includes(provider);
    });
  }, []);
  const addPost = () => {
    setPosts([
      ...posts,
      {
        index: posts.length,
        content: "",
        img_url: "https://picsum.photos/200/200",
        img_id: null,
      },
    ]);
  };

  const removePost = (post_index) => {
    posts.splice(post_index, 1);
    setPosts([...posts]);
  };

  const upPost = (post_index) => {
    if (post_index > 0) {
      const postUp = posts[post_index - 1];
      const postDown = posts[post_index];
      posts[post_index - 1] = postDown;
      posts[post_index] = postUp;
      setPosts([...posts]);
    }
  };

  const downPost = (post_index) => {
    if (post_index < posts.length - 1) {
      const postUp = posts[post_index];
      const postDown = posts[post_index + 1];
      posts[post_index] = postDown;
      posts[post_index + 1] = postUp;
      setPosts([...posts]);
    }
  };

  const selectImgPost = (post_index) => {
    if (wp) {
      wp.media.editor.open();
      wp.media.editor.send.attachment = function (props, attachment) {
        if (attachment) {
          posts[post_index].img_url = attachment.url;
          posts[post_index].img_id = attachment.id;
          setPosts([...posts]);
        }
      };
    }
  };

  const onChangeSelectDate = (e) => {
    setConfigDatetime({ ...configDatetime, date: e.target.value });
  };

  const onChangeTime = (e) => {
    setConfigDatetime({ ...configDatetime, time: e.target.value });
  };

  return (
    <>
      <div id="social-planner-recurrent">
        <div class="header">
          <h1>Planificador de publicaciones recurrentes</h1>
          <div>
            <span>Configuración de publicación</span>
            <div class="btn-group">
              {Object.keys(SC_PRE_DATA_PROVIDERS).map((provider) => (
                <label class="btn">
                  <input
                    type="checkbox"
                    name="sc_task_providers[]"
                    value={provider}
                  />
                  {SC_PRE_DATA_PROVIDERS[provider].title}
                </label>
              ))}
            </div>
            <select
              name="sc_date"
              value={configDatetime.date}
              onChange={onChangeSelectDate}
            >
              <option value="monday">Lunes</option>
              <option value="tuesday">Martes</option>
              <option value="wednesday">Miércoles</option>
              <option value="thursday">Jueves</option>
              <option value="friday">Viernes</option>
              <option value="saturday">Sábado</option>
              <option value="sunday">Domingo</option>
            </select>
            <input
              name="sc_time"
              type="time"
              value={configDatetime.time}
              onChange={onChangeTime}
            />
          </div>
        </div>
        {posts.map((post, index) => (
          <div key={post.index} class="post-box">
            <textarea name="sc_content_post[]">{post.content}</textarea>
            <input type="hidden" name="sc_img_id[]" value={post.img_id} />
            <input type="hidden" name="sc_index[]" value={index} />
            <input type="hidden" name="sc_img_url[]" value={post.img_url} />
            <img
              src={post.img_url}
              alt="post"
              onClick={() => selectImgPost(index)}
            />
            <div class="buttons">
              <b>{index + 1}</b>
              <a onClick={() => upPost(index)}>↑</a>
              <a onClick={() => downPost(index)}>↓</a>
              <a onClick={() => removePost(index)}>✕</a>
            </div>
          </div>
        ))}
        <div class="big-button" onClick={addPost}>
          AGREGAR NUEVO POST
        </div>
      </div>
    </>
  );
}

export default App;
